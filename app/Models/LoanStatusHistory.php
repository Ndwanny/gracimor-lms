<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanStatusHistory extends Model
{
    protected $table = 'loan_status_history';

    const UPDATED_AT = null;

    protected $fillable = [
        'loan_id', 'from_status', 'to_status',
        'notes', 'changed_by', 'is_system_action', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata'         => 'array',
            'is_system_action' => 'boolean',
        ];
    }

    public function loan(): BelongsTo      { return $this->belongsTo(Loan::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
