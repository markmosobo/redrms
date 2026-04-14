<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deposit;
use App\Models\User;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'refundable_amount',
        'refund_date',
        'status',
        'approved_by',
        'approved_at',
        'payment_reference',
        'remarks',
    ];

    protected $casts = [
        'refundable_amount' => 'decimal:2',
        'refund_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Refund belongs to a deposit
     */
    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    /**
     * Refund approved by a user
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}