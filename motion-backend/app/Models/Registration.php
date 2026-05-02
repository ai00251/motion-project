<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $primaryKey = 'registration_id';

    protected $fillable = [
        'event_id',
        'client_id',
        'registration_date'
    ];

    protected $casts = [
        'registration_date' => 'date'
    ];
}
