<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Borrower;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BorrowerService
{
    // ── Sequence counter ──────────────────────────────────────────────────

    /**
     * Generate the next borrower number in the format BRW-NNNNN.
     * Uses a table-level lock to prevent race conditions under concurrent requests.
     */
    public function generateBorrowerNumber(): string
    {
        $last = Borrower::withTrashed()
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('borrower_number');

        if (! $last) {
            return 'BRW-00001';
        }

        $n = (int) substr($last, 4);

        return 'BRW-' . str_pad($n + 1, 5, '0', STR_PAD_LEFT);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────

    /**
     * Register a new borrower.
     *
     * @param  array  $data  Validated request data
     * @param  User   $by    The officer registering the borrower
     * @return Borrower
     */
    public function create(array $data, User $by): Borrower
    {
        return DB::transaction(function () use ($data, $by) {
            $borrower = Borrower::create([
                ...$data,
                'borrower_number' => $this->generateBorrowerNumber(),
                'registered_by'   => $by->id,
                'kyc_status'      => 'pending',
            ]);

            AuditLog::record('borrower.created', $borrower, [], $borrower->toArray());

            return $borrower;
        });
    }

    /**
     * Update borrower profile fields.
     */
    public function update(Borrower $borrower, array $data): Borrower
    {
        return DB::transaction(function () use ($borrower, $data) {
            $old = $borrower->toArray();
            $borrower->update($data);
            AuditLog::record('borrower.updated', $borrower, $old, $borrower->fresh()->toArray());
            return $borrower->fresh();
        });
    }

    // ── KYC ───────────────────────────────────────────────────────────────

    /**
     * Mark a borrower as KYC-verified.
     */
    public function verifyKyc(Borrower $borrower, User $by): Borrower
    {
        if ($borrower->kyc_status === 'verified') {
            return $borrower;
        }

        $old = ['kyc_status' => $borrower->kyc_status];

        $borrower->update([
            'kyc_status'      => 'verified',
            'kyc_verified_at' => now(),
            'kyc_verified_by' => $by->id,
        ]);

        AuditLog::record('borrower.kyc_verified', $borrower, $old, ['kyc_status' => 'verified']);

        return $borrower->fresh();
    }

    /**
     * Reject KYC for a borrower.
     */
    public function rejectKyc(Borrower $borrower, User $by, string $reason): Borrower
    {
        $old = ['kyc_status' => $borrower->kyc_status];

        $borrower->update([
            'kyc_status'    => 'rejected',
            'internal_notes'=> $reason,
        ]);

        AuditLog::record('borrower.kyc_rejected', $borrower, $old, ['kyc_status' => 'rejected', 'reason' => $reason]);

        return $borrower->fresh();
    }

    // ── Search / listing ──────────────────────────────────────────────────

    /**
     * Paginated, filterable borrower list.
     *
     * @param  array  $filters  Keys: search, kyc_status, officer_id
     * @param  int    $perPage
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Borrower::with(['assignedOfficer'])
            ->withCount(['loans', 'activeLoans']);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['kyc_status'])) {
            $query->where('kyc_status', $filters['kyc_status']);
        }

        if (! empty($filters['officer_id'])) {
            $query->where('assigned_officer_id', $filters['officer_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Check whether a borrower has any active loans.
     */
    public function hasActiveLoan(Borrower $borrower): bool
    {
        return $borrower->activeLoans()->exists();
    }

    /**
     * Assign (or reassign) the responsible loan officer.
     */
    public function assignOfficer(Borrower $borrower, User $officer): Borrower
    {
        $old = ['assigned_officer_id' => $borrower->assigned_officer_id];
        $borrower->update(['assigned_officer_id' => $officer->id]);
        AuditLog::record('borrower.officer_assigned', $borrower, $old, ['assigned_officer_id' => $officer->id]);
        return $borrower->fresh();
    }
}
