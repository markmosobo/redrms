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
        'approval_status',
        'approved_by',
        'approved_at',
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