<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['code', 'currency_id', 'date', 'exchange_rate'];
    protected $casts = [
        'exchange_rate' => 'array'
    ];
}
