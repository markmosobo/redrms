<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenancy;
use App\Models\Deduction;
use App\Models\Unit;
use App\Models\User;

class Inspection extends Model
{
    protected $fillable = [
        'tenancy_id',
        'unit_id',
        'inspection_date',
        'notes',
        'created_by',
        'inspection_type',
        'status',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}