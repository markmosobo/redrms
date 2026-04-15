<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'resource_id',
        'resource_type',
        'read_at'
    ];

    /**
     * Notification belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}