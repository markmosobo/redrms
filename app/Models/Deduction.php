<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Deposit;
use App\Models\Inspection;
use App\Models\User;

class Deduction extends Model
{
    protected $fillable = [
        'deposit_id',
        'inspection_id',
        'description',
        'amount',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}