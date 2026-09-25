<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $fillable = [
        'first_name',
        'website',
        'email',
        'phone',
    ];
}
