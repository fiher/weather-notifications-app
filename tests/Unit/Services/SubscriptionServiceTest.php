<?php

namespace Tests\Unit\Services;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionService = new SubscriptionService();
    }

    public function test_get_all_subscriptions_returns_only_active(): void
    {
        Subscription::factory()->count(2)->create(['is_active' => true]);
        Subscription::factory()->create(['is_active' => false]);

        $subscriptions = $this->subscriptionService->getAllSubscriptions();

        $this->assertCount(2, $subscriptions);
        $this->assertTrue($subscriptions->every(fn($sub) => $sub->is_active));
    }

    public function test_get_subscription_by_id_returns_subscription(): void
    {
        $subscription = Subscription::factory()->create();

        $result = $this->subscriptionService->getSubscriptionById($subscription->id);

        $this->assertEquals($subscription->id, $result->id);
        $this->assertEquals($subscription->email, $result->email);
        $this->assertEquals($subscription->location, $result->location);
    }

    public function test_get_subscription_by_id_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->subscriptionService->getSubscriptionById(999);
    }

    public function test_create_subscription_creates_new_record(): void
    {
        $data = [
            'email' => 'test@example.com',
            'location' => 'London',
            'notification_time' => '09:00',
            'is_active' => true
        ];

        $subscription = $this->subscriptionService->createSubscription($data);

        $this->assertDatabaseHas('subscriptions', [
            'email' => $data['email'],
            'location' => $data['location'],
            'is_active' => true
        ]);
        $this->assertEquals($data['email'], $subscription->email);
        $this->assertEquals($data['location'], $subscription->location);
        $this->assertEquals($data['notification_time'], $subscription->notification_time->format('H:i'));
        $this->assertTrue($subscription->is_active);
    }

    public function test_update_subscription_modifies_record(): void
    {
        $subscription = Subscription::factory()->create();
        $data = [
            'location' => 'Paris',
            'notification_time' => '10:00'
        ];

        $updated = $this->subscriptionService->updateSubscription($subscription->id, $data);

        $this->assertEquals($data['location'], $updated->location);
        $this->assertEquals($data['notification_time'], $updated->notification_time->format('H:i'));
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'location' => $data['location']
        ]);
    }

    public function test_update_subscription_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->subscriptionService->updateSubscription(999, ['location' => 'Paris']);
    }

    public function test_delete_subscription_removes_record(): void
    {
        $subscription = Subscription::factory()->create();

        $result = $this->subscriptionService->deleteSubscription($subscription->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted($subscription);
    }

    public function test_delete_subscription_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->subscriptionService->deleteSubscription(999);
    }

    public function test_unsubscribe_deactivates_subscription(): void
    {
        $subscription = Subscription::factory()->create(['is_active' => true]);

        $result = $this->subscriptionService->unsubscribe($subscription->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'is_active' => false
        ]);
    }

    public function test_unsubscribe_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->subscriptionService->unsubscribe(999);
    }
} 