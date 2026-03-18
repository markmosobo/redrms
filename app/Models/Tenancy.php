<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Unit;

class Tenancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'unit_id',
        'start_date',
        'end_date',
        'deposit_amount',
        'status',
    ];

    /**
     * A tenancy belongs to a tenant (user)
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * A tenancy belongs to a unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}