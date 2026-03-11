<?php

namespace App\Jobs;

class MarkLoanOverdueOnThreshold implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(LoanOverdue $event): void
    {
        $loan    = $event->loan;
        $balance = $loan->loanBalance;
        $days    = $balance?->days_overdue ?? 0;

        // Create an internal alert for the officer when PAR thresholds are crossed
        $thresholds = [30 => 'PAR 30', 60 => 'PAR 60', 90 => 'PAR 90'];

        foreach ($thresholds as $threshold => $label) {
            if ($days === $threshold) {
                // Create notification record for officer
                $officerId = $loan->applied_by;
                if ($officerId) {
                    \App\Models\Notification::create([
                        'user_id'   => $officerId,
                        'type'      => 'loan_par_threshold',
                        'title'     => "{$label} threshold crossed",
                        'message'   => "Loan {$loan->loan_number} ({$loan->borrower?->full_name}) " .
                                       "has crossed the {$label} threshold. Immediate action required.",
                        'data'      => [
                            'loan_id'     => $loan->id,
                            'loan_number' => $loan->loan_number,
                            'days'        => $days,
                            'outstanding' => $balance?->total_outstanding,
                        ],
                    ]);
                }
            }
        }
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// Listener 6: SendOverdueEscalationAlert
//
// Fires on LoanEscalated — notifies the manager/CEO that a loan has been
// moved to legal/escalated status. Sends an internal notification + email.
// ═══════════════════════════════════════════════════════════════════════════════
