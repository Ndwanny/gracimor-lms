<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guarantor;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuarantorController extends Controller
{
    // GET /api/loans/{loan}/guarantors
    public function index(Loan $loan): JsonResponse
    {
        return response()->json($loan->guarantors()->with('borrower:id,first_name,last_name,phone_primary,nrc_number')->get());
    }

    // POST /api/loans/{loan}/guarantors
    public function store(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'guarantor_borrower_id' => 'nullable|exists:borrowers,id|different:' . $loan->borrower_id,
            'full_name'             => 'required_without:guarantor_borrower_id|string|max:200',
            'nrc_number'            => 'required_without:guarantor_borrower_id|string|max:30',
            'phone'                 => 'required_without:guarantor_borrower_id|string|max:20',
            'relationship'          => 'required|string|max:100',
            'address'               => 'nullable|string',
            'employer_name'         => 'nullable|string|max:150',
            'monthly_income'        => 'nullable|numeric|min:0',
        ]);

        // Map guarantor_borrower_id → borrower_id (column name in DB)
        $data = $validated;
        if (isset($data['guarantor_borrower_id'])) {
            $data['borrower_id'] = $data['guarantor_borrower_id'];
            unset($data['guarantor_borrower_id']);
        }

        $guarantor = $loan->guarantors()->create([
            ...$data,
            'added_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Guarantor added.', 'guarantor' => $guarantor], 201);
    }

    // DELETE /api/loans/{loan}/guarantors/{guarantor}
    public function destroy(Loan $loan, Guarantor $guarantor): JsonResponse
    {
        abort_unless($guarantor->loan_id === $loan->id, 404);

        $guarantor->delete();

        return response()->json(['message' => 'Guarantor removed.']);
    }
}
