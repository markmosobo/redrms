<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deposit;
use App\Models\Inspection;
use App\Models\User;

class Deduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'inspection_id',
        'description',
        'amount',
        'approved_by',
        'approved_at',
    ];

    /**
     * Deduction belongs to a deposit
     */
    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    /**
     * Deduction may belong to an inspection
     */
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Deduction approved by a user
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}