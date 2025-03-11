<?php

namespace App\Services;

use App\Interfaces\SubscriptionServiceInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function getAllSubscriptions(): Collection
    {
        return Subscription::where('is_active', true)->get();
    }

    public function getSubscriptionById(int $id): Subscription
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            Log::warning("Subscription not found: ID {$id}");
            throw new ModelNotFoundException("Subscription not found");
        }
        return $subscription;
    }

    public function createSubscription(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function updateSubscription(int $id, array $data): Subscription
    {
        $subscription = $this->getSubscriptionById($id);
        $subscription->update($data);
        return $subscription->fresh();
    }

    public function deleteSubscription(int $id): bool
    {
        $subscription = $this->getSubscriptionById($id);
        return $subscription->delete();
    }

    public function unsubscribe(int $id): bool
    {
        $subscription = $this->getSubscriptionById($id);
        return $subscription->update(['is_active' => false]);
    }
} 