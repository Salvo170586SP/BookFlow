<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'created_by',
        'loaned_at',
        'due_date',
        'returned_at',
        'renewal_count',
        'notes',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(LoanRenewal::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
