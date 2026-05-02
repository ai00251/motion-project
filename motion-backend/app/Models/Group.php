<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $primaryKey = 'group_id';

    protected $fillable = [
        'style_id',
        'member_count',
        'level',
        'title'
    ];

    public function style()
    {
        return $this->belongsTo(Style::class, 'style_id', 'style_id');
    }
}
