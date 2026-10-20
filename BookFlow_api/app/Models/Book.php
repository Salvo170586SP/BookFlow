<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'published_year',
        'publisher_id',
        'category_id',
        'member_id',
    ];

    // Un libro appartiene a un editore
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    // Un libro appartiene a una categoria
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    // Un libro ha uno o più autori (tramite la tabella author_book)
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(Copy::class);
    }

    public function scopeHasAvailableCopy(Builder $query): Builder
    {
        return $query->whereHas('copies', fn(Builder $copyQuery) => $copyQuery
            ->where('status', 'available')
            ->whereDoesntHave('loans', fn(Builder $loanQuery) => $loanQuery->whereNull('returned_at')));
    }
}
