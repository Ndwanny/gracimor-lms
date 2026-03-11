<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [

            // ── Payment ───────────────────────────────────────────────────────

            [
                'trigger_key' => 'payment_confirmation',
                'name'        => 'Payment Confirmation Receipt',
                'category'    => 'payment',
                'body'        =>
                    '{company_name}: Dear {first_name}, we confirm receipt of K{amount_paid} ' .
                    'for loan {loan_number}. Receipt: {receipt}. ' .
                    'Balance: K{balance_due}. Thank you.',
                // 160 chars with typical values — fits in 1 SMS page
            ],

            // ── Loan lifecycle ────────────────────────────────────────────────

            [
                'trigger_key' => 'loan_approved',
                'name'        => 'Loan Approved Notification',
                'category'    => 'loan',
                'body'        =>
                    '{company_name}: Dear {first_name}, your loan {loan_number} has been ' .
                    'APPROVED. K{amount_due} will be disbursed soon. ' .
                    'Contact: {officer_phone}.',
            ],

            [
                'trigger_key' => 'loan_disbursed',
                'name'        => 'Loan Disbursement Confirmation',
                'category'    => 'loan',
                'body'        =>
                    '{company_name}: Dear {first_name}, K{amount_due} has been disbursed ' .
                    'for loan {loan_number}. First repayment of K{total_due} is due ' .
                    '{due_date}. Officer: {officer_name} {officer_phone}.',
            ],

            [
                'trigger_key' => 'loan_closed',
                'name'        => 'Loan Fully Settled',
                'category'    => 'loan',
                'body'        =>
                    '{company_name}: Congratulations {first_name}! Loan {loan_number} is ' .
                    'fully repaid. Your collateral will be released within 7 working days. ' .
                    'Thank you for choosing {company_name}.',
            ],

            // ── Pre-due reminders ─────────────────────────────────────────────

            [
                'trigger_key' => 'pre_due_7_days',
                'name'        => 'Reminder — 7 Days Before Due Date',
                'category'    => 'reminder',
                'body'        =>
                    '{company_name}: Dear {first_name}, instalment {instalment_no} of ' .
                    'K{amount_due} on loan {loan_number} is due on {due_date} ' .
                    '(7 days). Please ensure funds are ready. ' .
                    'Queries: {officer_phone}.',
            ],

            [
                'trigger_key' => 'pre_due_3_days',
                'name'        => 'Reminder — 3 Days Before Due Date',
                'category'    => 'reminder',
                'body'        =>
                    '{company_name}: Dear {first_name}, your instalment of K{amount_due} ' .
                    'for loan {loan_number} is due in 3 days on {due_date}. ' .
                    'Avoid penalties by paying on time. {officer_phone}.',
            ],

            [
                'trigger_key' => 'pre_due_1_day',
                'name'        => 'Reminder — 1 Day Before Due Date',
                'category'    => 'reminder',
                'body'        =>
                    '{company_name}: {first_name}, your payment of K{amount_due} ' .
                    'for {loan_number} is due TOMORROW, {due_date}. ' .
                    'Pay today to avoid a late penalty. {officer_phone}.',
            ],

            [
                'trigger_key' => 'due_today',
                'name'        => 'Reminder — Due Today',
                'category'    => 'reminder',
                'body'        =>
                    '{company_name}: {first_name}, K{amount_due} for loan {loan_number} ' .
                    'is DUE TODAY, {due_date}. Pay today to avoid penalty charges. ' .
                    'Contact: {officer_phone}.',
            ],

            // ── Overdue reminders ─────────────────────────────────────────────

            [
                'trigger_key' => 'overdue_1_day',
                'name'        => 'Overdue Notice — 1 Day',
                'category'    => 'overdue',
                'body'        =>
                    '{company_name}: Dear {first_name}, your instalment of K{amount_due} ' .
                    'on loan {loan_number} was due {due_date} and is now overdue. ' .
                    'Please pay urgently to avoid penalty charges. {officer_phone}.',
            ],

            [
                'trigger_key' => 'overdue_7_days',
                'name'        => 'Overdue Notice — 7 Days',
                'category'    => 'overdue',
                'body'        =>
                    '{company_name}: OVERDUE NOTICE — {first_name}, loan {loan_number} ' .
                    'is 7 days overdue. Outstanding: K{total_due} incl. penalties of ' .
                    'K{total_penalties}. Contact {officer_name} NOW: {officer_phone}.',
            ],

            [
                'trigger_key' => 'overdue_14_days',
                'name'        => 'Overdue Notice — 14 Days',
                'category'    => 'overdue',
                'body'        =>
                    '{company_name}: URGENT — {first_name}, loan {loan_number} is ' .
                    '14 DAYS OVERDUE. Total due: K{total_due}. Failure to pay may ' .
                    'result in legal action and collateral recovery. ' .
                    'Call {officer_phone} immediately.',
            ],

            [
                'trigger_key' => 'overdue_30_days',
                'name'        => 'Overdue Notice — 30 Days',
                'category'    => 'overdue',
                'body'        =>
                    '{company_name}: FINAL NOTICE — {first_name}, loan {loan_number} ' .
                    'is 30 DAYS OVERDUE. Amount: K{total_due}. This matter has been ' .
                    'referred for collections. Settle now to avoid legal proceedings. ' .
                    '{company_phone}.',
            ],

            // ── Escalation ────────────────────────────────────────────────────

            [
                'trigger_key' => 'escalation_notice',
                'name'        => 'Legal Escalation Notice (to Borrower)',
                'category'    => 'overdue',
                'body'        =>
                    '{company_name}: Dear {first_name} {last_name}, loan {loan_number} ' .
                    '({days_overdue} days overdue, K{total_due}) has been referred for ' .
                    'legal recovery. Settle immediately to avoid court proceedings. ' .
                    '{company_phone}.',
            ],

        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['trigger_key' => $template['trigger_key']],
                [
                    'name'     => $template['name'],
                    'body'     => $template['body'],
                    'category' => $template['category'],
                ]
            );
        }

        $this->command->info('✓ ' . count($templates) . ' SMS templates seeded.');
    }
}
