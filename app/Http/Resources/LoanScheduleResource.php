<?php

namespace App\Http\Resources;

class LoanScheduleResource extends GracimorResource
{
    public function toArray($request): array
    {
        // How much of this instalment is still outstanding
        $principalDue = max(0, ($this->principal_component ?? 0) - ($this->principal_paid ?? 0));
        $interestDue  = max(0, ($this->interest_component  ?? 0) - ($this->interest_paid  ?? 0));
        $totalDue     = $principalDue + $interestDue;

        // Days overdue for this specific instalment
        $daysOverdue  = 0;
        if (in_array($this->status, ['overdue', 'partial']) && $this->due_date) {
            $daysOverdue = max(0, now()->diffInDays($this->due_date, false) * -1);
        }

        return [
            'id'                  => $this->id,
            'instalment_number'   => (int) $this->instalment_number,
            'due_date'            => $this->d($this->due_date),
            'due_date_iso'        => $this->dt($this->due_date),

            // Scheduled amounts
            'scheduled' => [
                'total'     => $this->money(($this->principal_component ?? 0) + ($this->interest_component ?? 0)),
                'principal' => $this->money($this->principal_component),
                'interest'  => $this->money($this->interest_component),
            ],

            // Paid-to-date on this instalment
            'paid' => [
                'total'     => $this->money(($this->principal_paid ?? 0) + ($this->interest_paid ?? 0)),
                'principal' => $this->money($this->principal_paid),
                'interest'  => $this->money($this->interest_paid),
            ],

            // Still outstanding on this instalment
            'outstanding' => [
                'total'     => $this->money($totalDue),
                'principal' => $this->money($principalDue),
                'interest'  => $this->money($interestDue),
            ],

            // Opening balance (principal outstanding before this instalment)
            'opening_balance'  => $this->money($this->opening_balance),
            'closing_balance'  => $this->money($this->closing_balance),

            'status'       => $this->statusBadge($this->status ?? 'pending'),
            'days_overdue' => $daysOverdue,
            'paid_at'      => $this->dt($this->paid_at),
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanStatusHistoryResource
// One row per status transition on a loan
// Used by: LoanResource (whenLoaded), LoanController::activity
// ═══════════════════════════════════════════════════════════════════════════════
