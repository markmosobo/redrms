<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Unit;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'manager_id',
        'property_name',
        'location',
        'description',
    ];

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }
    public function units()
    {
        return $this->hasMany(Unit::class);
    } 
    
    // Property.php
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}