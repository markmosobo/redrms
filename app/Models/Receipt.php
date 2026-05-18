<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Deposit;

class Receipt extends Model
{
    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'receipt_number',
        'payment_method',
        'mpesa_code',
        'amount',
        'issued_at',
        'deposit_id',
        'data',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * A receipt belongs to one deposit
     */
    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    /**
     * Convenience: get tenant directly from receipt
     * (receipt → deposit → tenancy → tenant)
     */
    public function tenant()
    {
        return $this->deposit?->tenancy?->tenant();
    }

    /**
     * Convenience: get tenancy
     */
    public function tenancy()
    {
        return $this->deposit?->tenancy();
    }
}