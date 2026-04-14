<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenancy;
use App\Models\Deduction;
use App\Models\Refund;

class Deposit extends Model
{
    protected $fillable = [
        'tenancy_id',
        'amount_received',
        'current_balance',
        'received_date',
        'required_amount',
        'status',
        'remarks',
    ];

    protected $casts = [
        'amount_received' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'received_date' => 'date',
    ];

    // 🔗 Deposit belongs to a tenancy
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    // 🔗 Deposit has many deductions
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    // 🔗 Deposit has many refunds (history-safe design)
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    // 🧠 Helper: calculate remaining balance dynamically
    public function getTotalDeductionsAttribute()
    {
        return $this->deductions()->sum('amount');
    }

    public function getRefundableAmountAttribute()
    {
        return $this->amount_received - $this->total_deductions;
    }
}