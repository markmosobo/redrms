<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'ip_address',
    ];

    /**
     * Audit log belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}