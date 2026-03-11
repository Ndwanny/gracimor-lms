<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guarantor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_id', 'borrower_id', 'full_name', 'nrc_number', 'phone',
        'email', 'address', 'relationship', 'employment_status',
        'employer_name', 'monthly_income', 'status', 'notes', 'added_by',
    ];

    protected function casts(): array
    {
        return ['monthly_income' => 'decimal:2'];
    }

    public function loan(): BelongsTo     { return $this->belongsTo(Loan::class); }
    public function borrower(): BelongsTo { return $this->belongsTo(Borrower::class); }
    public function addedBy(): BelongsTo  { return $this->belongsTo(User::class, 'added_by'); }
}
