<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'event_id';
    
    protected $fillable = [
        'style_id',
        'duration_minutes',
        'hall',
        'level',
        'start_time',
        'start_date',
        'status',
        'capacity'
    ];

    public function style()
    {
        return $this->belongsTo(Style::class, 'style_id', 'style_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'event_id', 'event_id');
    }
}
