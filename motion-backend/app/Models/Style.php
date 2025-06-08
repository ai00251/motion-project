<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Style extends Model
{
    protected $table = 'styles';
    protected $primaryKey = 'style_id';
    
    protected $fillable = [
        'title',
        'description'
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'style_id', 'style_id');
    }
}
