<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $primaryKey = 'admin_id';
    protected $table = 'administration';

    protected $fillable = [
    'user_id',
    'position_type',
    'photo_url',        
    'bio_description'   
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
