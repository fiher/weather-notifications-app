<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'city' => $this->city,
            'country_code' => $this->country_code,
            'frequency' => $this->frequency,
            'notification_time' => $this->notification_time?->format('H:i:s'),
            'is_active' => $this->is_active,
            'last_notification_sent_at' => $this->last_notification_sent_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
} 