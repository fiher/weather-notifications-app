<?php

namespace App\Interfaces;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionServiceInterface
{
    /**
     * Get all subscriptions
     *
     * @return Collection<Subscription>
     */
    public function getAllSubscriptions(): Collection;

    /**
     * Create a new subscription
     *
     * @param array $data
     * @return Subscription
     */
    public function createSubscription(array $data): Subscription;

    /**
     * Get subscription by ID
     *
     * @param int $id
     * @return Subscription
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSubscriptionById(int $id): Subscription;

    /**
     * Update subscription
     *
     * @param int $id
     * @param array $data
     * @return Subscription
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateSubscription(int $id, array $data): Subscription;

    /**
     * Delete subscription
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteSubscription(int $id): bool;

    /**
     * Unsubscribe from notifications
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unsubscribe(int $id): bool;
} 