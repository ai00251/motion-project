<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;  

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;  

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'name',
        'surname',
        'birth_date',
        'email',
        'phone_number',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

}
