<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrower;
use App\Models\Document;
use App\Services\BorrowerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BorrowerController extends Controller
{
    public function __construct(protected BorrowerService $borrowerService) {}

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/borrowers
    // Query params: search, kyc_status, employment_status, assigned_officer_id,
    //               sort_by, sort_dir, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Borrower::with(['assignedOfficer:id,name', 'loans' => fn ($q) => $q->active()])
            ->withCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()])
            ->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name',   'like', "%$search%")
                  ->orWhere('borrower_number', 'like', "%$search%")
                  ->orWhere('nrc_number',      'like', "%$search%")
                  ->orWhere('phone_primary',   'like', "%$search%");
            });
        }

        if ($request->kyc_status) {
            $query->where('kyc_status', $request->kyc_status);
        }

        if ($request->employment_status) {
            $query->where('employment_status', $request->employment_status);
        }

        if ($request->assigned_officer_id) {
            $query->where('assigned_officer_id', $request->assigned_officer_id);
        }

        $allowedSorts = ['created_at', 'first_name', 'last_name', 'borrower_number'];
        $sortBy  = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $borrowers = $query->paginate($request->per_page ?? 20);

        return response()->json($borrowers);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/borrowers
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'nrc_number'         => 'required|string|max:30|unique:borrowers,nrc_number',
            'date_of_birth'      => 'nullable|date|before:today',
            'gender'             => 'nullable|in:male,female,other',
            'phone_primary'      => 'required|string|max:20',
            'phone_secondary'    => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:150',
            'residential_address'=> 'required|string',
            'city_town'          => 'required|string|max:100',
            'employment_status'  => 'required|in:employed,self_employed,unemployed,retired',
            'employer_name'      => 'nullable|string|max:150',
            'job_title'          => 'nullable|string|max:100',
            'monthly_income'     => 'nullable|numeric|min:0',
            'work_phone'         => 'nullable|string|max:20',
            'work_address'       => 'nullable|string',
            'assigned_officer_id'=> 'nullable|exists:users,id',
            'internal_notes'     => 'nullable|string',
        ]);

        $borrower = $this->borrowerService->create($validated, Auth::user());

        return response()->json([
            'message'  => 'Borrower registered successfully.',
            'borrower' => $borrower->load('assignedOfficer:id,name'),
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/borrowers/{borrower}
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Borrower $borrower): JsonResponse
    {
        $borrower->load([
            'assignedOfficer:id,name',
            'loans.loanProduct:id,name',
            'loans.loanBalance',
            'loans.collateralAsset',
            'loans.payments',
            'documents',
            'guarantors.loan:id,loan_number',
            'collateralAssets',
        ])->loadCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()]);

        return response()->json($borrower);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /api/borrowers/{borrower}
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, Borrower $borrower): JsonResponse
    {
        $validated = $request->validate([
            'first_name'         => 'sometimes|required|string|max:100',
            'last_name'          => 'sometimes|required|string|max:100',
            'nrc_number'         => "sometimes|required|string|max:30|unique:borrowers,nrc_number,{$borrower->id}",
            'date_of_birth'      => 'sometimes|required|date|before:today',
            'gender'             => 'sometimes|required|in:male,female,other',
            'phone_primary'      => 'sometimes|required|string|max:20',
            'phone_secondary'    => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:150',
            'residential_address'=> 'sometimes|required|string',
            'city_town'          => 'sometimes|required|string|max:100',
            'employment_status'  => 'sometimes|required|in:employed,self_employed,unemployed,retired',
            'employer_name'      => 'nullable|string|max:150',
            'job_title'          => 'nullable|string|max:100',
            'monthly_income'     => 'nullable|numeric|min:0',
            'work_phone'         => 'nullable|string|max:20',
            'work_address'       => 'nullable|string',
            'assigned_officer_id'=> 'nullable|exists:users,id',
            'internal_notes'     => 'nullable|string',
        ]);

        $borrower = $this->borrowerService->update($borrower, $validated);

        return response()->json([
            'message'  => 'Borrower updated successfully.',
            'borrower' => $borrower->load('assignedOfficer:id,name'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/borrowers/{borrower}
    // Soft-deletes only. Blocked if borrower has active loans.
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(Borrower $borrower): JsonResponse
    {
        if ($borrower->loans()->active()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a borrower with active loans.',
            ], 422);
        }

        $borrower->delete();

        return response()->json(['message' => 'Borrower deleted.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/borrowers/{borrower}/verify-kyc
    // Body: { notes? }
    // ──────────────────────────────────────────────────────────────────────────
    public function verifyKyc(Request $request, Borrower $borrower): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $borrower->update([
            'kyc_status'     => 'verified',
            'kyc_verified_at'=> now(),
            'kyc_verified_by'=> Auth::id(),
            'internal_notes' => $request->notes
                ? ($borrower->internal_notes . "\n[KYC] " . $request->notes)
                : $borrower->internal_notes,
        ]);

        return response()->json([
            'message'  => 'KYC verified successfully.',
            'borrower' => $borrower->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/borrowers/{borrower}/documents
    // Multipart: file, document_type, expiry_date?
    // ──────────────────────────────────────────────────────────────────────────
    public function uploadDocument(Request $request, Borrower $borrower): JsonResponse
    {
        $request->validate([
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_type' => 'required|in:national_id,payslip,bank_statement,vehicle_logbook,vehicle_photos,land_title_deed,valuation_report,loan_agreement,guarantor_id,proof_of_residence,other',
        ]);

        $path = $request->file('file')->store("borrowers/{$borrower->id}/documents", 'local');

        $document = $borrower->documents()->create([
            'document_type'   => $request->document_type,
            'display_name'    => $request->file('file')->getClientOriginalName(),
            'file_path'       => $path,
            'file_name'       => $request->file('file')->getClientOriginalName(),
            'mime_type'       => $request->file('file')->getMimeType(),
            'file_size_bytes' => $request->file('file')->getSize(),
            'disk'            => 'local',
            'uploaded_by'     => Auth::id(),
        ]);

        return response()->json([
            'message'  => 'Document uploaded.',
            'document' => $document,
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/borrowers/{borrower}/documents/{document}/download
    // ──────────────────────────────────────────────────────────────────────────
    public function downloadDocument(Borrower $borrower, Document $document)
    {
        abort_unless((int) $document->documentable_id === $borrower->id &&
                     $document->documentable_type === Borrower::class, 403);

        $disk = $document->disk ?? 'local';
        abort_unless(Storage::disk($disk)->exists($document->file_path), 404);
        return Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/borrowers/{borrower}/documents/{document}/view
    // Serves the file inline (for browser preview of PDFs/images)
    // ──────────────────────────────────────────────────────────────────────────
    public function viewDocument(Borrower $borrower, Document $document)
    {
        abort_unless((int) $document->documentable_id === $borrower->id &&
                     $document->documentable_type === Borrower::class, 403);

        $disk = $document->disk ?? 'local';
        abort_unless(Storage::disk($disk)->exists($document->file_path), 404);
        return Storage::disk($disk)->response($document->file_path);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/borrowers/{borrower}/statement
    // Summary of all loans, payments, balances for a borrower
    // ──────────────────────────────────────────────────────────────────────────
    public function statement(Borrower $borrower): JsonResponse
    {
        $loans = $borrower->loans()
            ->with(['loanProduct:id,name', 'loanBalance', 'payments', 'penalties'])
            ->get();

        $summary = [
            'total_loans'       => $loans->count(),
            'active_loans'      => $loans->where('status', 'active')->count(),
            'total_borrowed'    => $loans->sum('principal_amount'),
            'total_paid'        => $loans->sum(fn ($l) => $l->payments->sum('amount_received')),
            'total_outstanding' => $loans->whereIn('status', ['active', 'overdue'])->sum(fn ($l) => $l->loanBalance?->total_outstanding ?? 0),
            'total_penalties'   => $loans->sum(fn ($l) => $l->penalties->where('status', 'outstanding')->sum('penalty_amount')),
        ];

        return response()->json([
            'borrower' => $borrower->only('id', 'borrower_number', 'first_name', 'last_name', 'phone_primary'),
            'summary'  => $summary,
            'loans'    => $loans,
        ]);
    }
}
