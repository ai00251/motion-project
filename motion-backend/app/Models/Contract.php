<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contracts';
    protected $primaryKey = 'contract_id';
    public $timestamps = false;  
    
    protected $fillable = [
        'client_id',
        'group_id',
        'registration_date',
        'end_date',
        'monthly_fee'
    ];
    
    protected $casts = [
        'registration_date' => 'date',
        'end_date' => 'date',
        'monthly_fee' => 'decimal:2'
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }
    
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }
}
