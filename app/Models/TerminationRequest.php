<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerminationRequest extends Model
{
    protected $fillable = [
        'tenancy_id',
        'requested_by',
        'status',
        'requested_end_date',
        'reason',
        'processed_at',
        'processed_by'
    ];

    /*
    |----------------------------------------------------
    | RELATIONSHIPS
    |----------------------------------------------------
    */

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /*
    |----------------------------------------------------
    | OPTIONAL: STATUS HELPERS (cleaner logic)
    |----------------------------------------------------
    */

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}