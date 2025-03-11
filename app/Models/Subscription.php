<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Subscription extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'email',
        'location',
        'notification_time',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notification_time' => 'datetime',
    ];

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
} 