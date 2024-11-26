<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $casts = [
        'exchange_rate' => 'array',
    ];
}
