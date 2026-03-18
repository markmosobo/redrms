<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deduction;
use App\Models\Tenancy;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'amount_received',
        'received_date',
        'status',
    ];

    /**
     * A deposit belongs to a tenancy
     */
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Optional: Get deductions related to this deposit
     */
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}