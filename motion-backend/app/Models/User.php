<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Client;
use App\Models\Admin;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    // Add this to automatically include role in JSON responses
    protected $appends = ['role'];

    // relationship to client profile
    public function client()
    {
        return $this->hasOne(Client::class, 'user_id', 'user_id');
    }

    // relationship to admin profile
    public function administration()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

    // direct contracts through client
    public function contracts()
    {
        return $this->hasManyThrough(
            Contract::class,
            Client::class,
            'user_id',   // foreign key on clients table
            'client_id', // foreign key on contracts table
            'user_id',   // local key on users table
            'client_id'  // local key on clients table
        );
    }

    // direct registrations through client
    public function registrations()
    {
        return $this->hasManyThrough(
            Registration::class,
            Client::class,
            'user_id',   // foreign key on clients table
            'client_id', // foreign key on registrations table
            'user_id',   // local key on users table
            'client_id'  // local key on clients table
        );
    }

    // helper methods for role checking
    public function isClient()
    {
        return Client::where('user_id', $this->user_id)->exists();
    }

    public function isAdmin()
    {
        return Admin::where('user_id', $this->user_id)->exists();
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

    public function isRegularUser()
    {
        return !$this->isClient() && !$this->isAdmin();
    }

    public function getRole()
    {
        if ($this->isAdmin()) return 'admin';
        if ($this->isClient()) return 'client';
        return 'user';
    }

    public function getRoleAttribute()
    {
        return $this->getRole();
    }
}
