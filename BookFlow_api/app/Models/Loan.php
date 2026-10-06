<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = ['copy_id', 'user_id', 'loaned_at', 'due_at', 'returned_at'];

    // Le colonne data diventano oggetti Carbon (con ->isPast(), ->diffInDays()...)
    protected function casts(): array
    {
        return [
            'loaned_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(Copy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope: Loan::active()->get() → solo i prestiti non ancora restituiti
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('returned_at');
    }

    // Scope: Loan::overdue()->get() → attivi con scadenza superata
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('returned_at')->where('due_at', '<', now());
    }
}
