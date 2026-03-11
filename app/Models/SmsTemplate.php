<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SmsTemplate extends Model
{
    protected $fillable = [
        'trigger_key',
        'name',
        'body',
        'category',
        'is_active',
        'char_count',
        'sms_pages',
        'last_edited_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function lastEditedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    // ── Boot: auto-update char_count and sms_pages on save ───────────────────

    protected static function booted(): void
    {
        static::saving(function (SmsTemplate $template) {
            // Strip variables for length calculation (replace with typical value lengths)
            $sample = preg_replace('/\{[^}]+\}/', 'XXXXX', $template->body);
            $len    = mb_strlen($sample);

            $template->char_count = $len;
            $template->sms_pages  = $len <= 160 ? 1 : (int) ceil($len / 153);
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Extract all variable placeholders from the template body.
     * Returns an array like ['first_name', 'loan_number', 'amount_due']
     */
    public function extractVariables(): array
    {
        preg_match_all('/\{([^}]+)\}/', $this->body, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Render a preview of this template by substituting demo values.
     */
    public function previewBody(): string
    {
        $demos = [
            'first_name'      => 'Mwansa',
            'last_name'       => 'Chanda',
            'loan_number'     => 'GRS-2026-00042',
            'amount_due'      => '5,231.44',
            'total_due'       => '38,200.00',
            'due_date'        => '26 Feb 2026',
            'amount_paid'     => '5,000.00',
            'receipt'         => 'RCT-2026-01234',
            'balance_due'     => '33,200.00',
            'instalment_no'   => '4',
            'days_overdue'    => '7',
            'penalty_amount'  => '250.00',
            'penalty_rate'    => '2.5',
            'total_penalties' => '750.00',
            'officer_name'    => 'E. Mwale',
            'officer_phone'   => '+260977000001',
            'company_name'    => config('gracimor.company_name', 'Gracimor Loans'),
            'company_phone'   => config('gracimor.office_phone', '+260211000001'),
        ];

        $body = $this->body;
        foreach ($demos as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        return $body;
    }

    /**
     * Calculate the actual character count when rendered with max-length values.
     * Used to warn staff if a template will span multiple SMS pages.
     */
    public function estimatedMaxLength(): int
    {
        $maxValues = [
            'first_name'      => 'Mwanachilumbamasiku',  // 19 chars
            'last_name'       => 'Mwanachilumbamasiku',
            'loan_number'     => 'GRS-2026-99999',
            'amount_due'      => '999,999.99',
            'total_due'       => '999,999.99',
            'due_date'        => '28 February 2026',
            'amount_paid'     => '999,999.99',
            'receipt'         => 'RCT-2026-99999',
            'balance_due'     => '999,999.99',
            'instalment_no'   => '60',
            'days_overdue'    => '90',
            'penalty_amount'  => '99,999.99',
            'penalty_rate'    => '5.00',
            'total_penalties' => '99,999.99',
            'officer_name'    => 'Mwanachilumbamasiku E.',
            'officer_phone'   => '+260977000001',
            'company_name'    => 'Gracimor Microfinance Ltd',
            'company_phone'   => '+260211000001',
        ];

        $body = $this->body;
        foreach ($maxValues as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }
        return mb_strlen($body);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
