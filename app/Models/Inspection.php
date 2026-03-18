<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Deduction;
use App\Models\Tenancy;
use App\Models\User;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'inspection_date',
        'notes',
        'created_by',
        'inspection_type',
    ];

    /**
     * Inspection belongs to a tenancy
     */
    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    /**
     * Inspection was created by a user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Optional: Get deductions linked to this inspection
     */
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}