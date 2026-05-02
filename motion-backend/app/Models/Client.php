<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $primaryKey = 'client_id';

    protected $fillable = [
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'client_id', 'client_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'client_id', 'client_id');
    }
}
