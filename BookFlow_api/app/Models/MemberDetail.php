<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDetail extends Model
{
    protected $fillable = [
        'phone',
        'address',
        'birth_city',
        'birth_date',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class);
    }
}
