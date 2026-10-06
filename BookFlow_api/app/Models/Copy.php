<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Copy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'barcode',
        'status'
    ];

    // La copia appartiene a un libro (l'opera)
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // Tutti i prestiti di questa copia, anche quelli passati (lo storico)
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // Il prestito attualmente aperto: restituisce il Loan oppure null
    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)->whereNull('returned_at');
    }
}
