<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRenewal extends Model
{
    protected $fillable = [
        'loan_id',
        'renewed_by',
        'previous_due_date',
        'new_due_date',
        'renewed_at',
        'notes',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renewed_by');
    }
}
