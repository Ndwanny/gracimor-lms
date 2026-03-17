<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/payments
    // Query params: search, loan_id, borrower_id, payment_method, date_from,
    //               date_to, officer_id, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with([
            'loan:id,loan_number,borrower_id',
            'loan.borrower:id,borrower_number,first_name,last_name,phone_primary',
            'recordedBy:id,name',
            'paymentAllocations',
            'loanSchedule:id,instalment_number',
        ])->latest('payment_date');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%$search%")
                  ->orWhereHas('loan', fn ($lq) =>
                      $lq->where('loan_number', 'like', "%$search%")
                         ->orWhereHas('borrower', fn ($bq) =>
                             $bq->where('first_name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%")
                         )
                  );
            });
        }

        if ($request->loan_id) {
            $query->where('loan_id', $request->loan_id);
        }

        if ($request->borrower_id) {
            $query->whereHas('loan', fn ($q) => $q->where('borrower_id', $request->borrower_id));
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->officer_id) {
            $query->where('recorded_by', $request->officer_id);
        }

        return response()->json($query->paginate($request->per_page ?? 25));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/payments
    // Record a payment against a loan
    // Body: { loan_id, amount, payment_method, payment_date, reference?, notes? }
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_id'        => 'required|exists:loans,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'payment_date'   => 'required|date|before_or_equal:today',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        $loan = Loan::findOrFail($validated['loan_id']);

        if (!in_array($loan->status, ['active', 'overdue'])) {
            return response()->json(['message' => 'Payments can only be recorded against active or overdue loans.'], 422);
        }

        $data = [
            'amount_received'   => $validated['amount'],
            'payment_method'    => $validated['payment_method'],
            'payment_date'      => $validated['payment_date'],
            'payment_reference' => $validated['reference'] ?? null,
            'notes'             => $validated['notes'] ?? null,
        ];

        $payment = $this->paymentService->record($loan, Auth::user(), $data);

        return response()->json([
            'message'     => 'Payment recorded successfully.',
            'payment'     => $payment->load('paymentAllocations'),
            'allocations' => $payment->paymentAllocations,
            'loan_status' => $loan->fresh()->status,
            'receipt'     => $payment->receipt_number,
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/payments/{payment}
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Payment $payment): JsonResponse
    {
        $payment->load([
            'loan.borrower',
            'loan.loanProduct:id,name',
            'paymentAllocations',
            'recordedBy:id,name',
        ]);

        return response()->json($payment);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/payments/{payment}/receipt
    // Returns receipt data formatted for printing/PDF
    // ──────────────────────────────────────────────────────────────────────────
    public function receipt(Payment $payment): JsonResponse
    {
        $payment->load([
            'loan.borrower',
            'loan.loanProduct:id,name',
            'paymentAllocations',
            'recordedBy:id,name',
        ]);

        $receipt = [
            'receipt_number'  => $payment->receipt_number,
            'generated_at'    => now()->toDateTimeString(),
            'payment_date'    => $payment->payment_date->format('d M Y'),
            'borrower'        => [
                'name'    => $payment->loan->borrower->full_name,
                'number'  => $payment->loan->borrower->borrower_number,
                'phone'   => $payment->loan->borrower->phone_primary,
            ],
            'loan'            => [
                'number'  => $payment->loan->loan_number,
                'product' => $payment->loan->loanProduct->name,
            ],
            'amount'          => $payment->amount_received,
            'payment_method'  => ucfirst(str_replace('_', ' ', $payment->payment_method)),
            'reference'       => $payment->payment_reference,
            'allocations'     => $payment->paymentAllocations->map(fn ($a) => [
                'type'   => $a->allocation_type,
                'amount' => $a->amount,
            ]),
            'recorded_by'     => $payment->recordedBy->name,
        ];

        return response()->json($receipt);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/payments/{payment}
    // Reversal — only allowed within same day, by manager+
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(Request $request, Payment $payment): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        if ($payment->payment_date->toDateString() !== today()->toDateString()) {
            return response()->json(['message' => 'Payments can only be reversed on the same day they were recorded.'], 422);
        }

        $this->paymentService->reverse($payment, Auth::id(), $request->reason);

        return response()->json(['message' => 'Payment reversed successfully.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/payments/summary
    // Aggregated totals for a date range (used by Collections report)
    // ──────────────────────────────────────────────────────────────────────────
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $summary = $this->paymentService->periodSummary(
            $request->date_from,
            $request->date_to,
            $request->officer_id,
        );

        return response()->json($summary);
    }
}
