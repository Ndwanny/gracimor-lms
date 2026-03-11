<?php

namespace App\Http\Resources;

class LoanBalanceResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            'principal_balance'    => $this->money($this->principal_balance),
            'interest_balance'     => $this->money($this->interest_balance),
            'penalty_balance'      => $this->money($this->penalty_balance),
            'total_outstanding'    => $this->money($this->total_outstanding),
            'total_paid'           => $this->money($this->total_paid),
            'days_overdue'         => (int) ($this->days_overdue ?? 0),
            'instalments_overdue'  => (int) ($this->instalments_overdue ?? 0),
            'instalments_remaining'=> (int) ($this->instalments_remaining ?? 0),
            'daily_penalty_accrual'=> $this->money($this->daily_penalty_accrual),
            'last_payment_date'    => $this->d($this->last_payment_date),
            'last_payment_amount'  => $this->money($this->last_payment_amount),
            'recalculated_at'      => $this->dt($this->recalculated_at),
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanScheduleResource
// Each row represents one instalment in the repayment schedule
// Used by: LoanController::schedule, LoanResource (whenLoaded)
// ═══════════════════════════════════════════════════════════════════════════════
