<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Subscription;
use App\Services\WeatherService;
use App\DTOs\Weather\WeatherResponseDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Mockery;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherService = Mockery::mock(WeatherService::class);
        $this->app->instance(WeatherService::class, $this->weatherService);
        Notification::fake();
    }

    public function test_index_returns_active_subscriptions(): void
    {
        Subscription::factory()->create(['is_active' => true]);
        Subscription::factory()->create(['is_active' => true]);
        Subscription::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/subscriptions');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_store_creates_subscription_successfully(): void
    {
        $data = [
            'email' => 'test@example.com',
            'location' => 'London',
            'notification_time' => '09:00'
        ];

        $mockWeatherData = WeatherResponseDTO::fromArray([
            'location' => [
                'name' => 'London',
                'country' => 'United Kingdom',
                'region' => 'City of London',
                'lat' => '51.517',
                'lon' => '-0.106',
                'timezone_id' => 'Europe/London',
                'localtime' => '2024-01-20 10:00',
                'localtime_epoch' => 1705744800,
                'utc_offset' => '0.0'
            ],
            'current' => [
                'observation_time' => '09:00 AM',
                'temperature' => 20,
                'weather_code' => 113,
                'weather_icons' => ['https://example.com/sunny.png'],
                'weather_descriptions' => ['Sunny'],
                'wind_speed' => 10,
                'wind_degree' => 180,
                'wind_dir' => 'N',
                'pressure' => 1015,
                'precip' => 0,
                'humidity' => 70,
                'cloudcover' => 25,
                'feelslike' => 19,
                'uv_index' => 5,
                'visibility' => 10,
                'is_day' => 'yes'
            ]
        ]);

        $this->weatherService
            ->shouldReceive('getWeatherData')
            ->once()
            ->andReturn($mockWeatherData);

        $response = $this->postJson('/api/subscriptions', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Subscription created successfully',
                'data' => [
                    'email' => $data['email'],
                    'location' => $data['location']
                ]
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'email' => $data['email'],
            'location' => $data['location'],
            'is_active' => true
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/subscriptions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'location', 'notification_time']);
    }

    public function test_store_validates_email_format(): void
    {
        $data = [
            'email' => 'invalid-email',
            'location' => 'London',
            'notification_time' => '09:00'
        ];

        $response = $this->postJson('/api/subscriptions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_validates_notification_time_format(): void
    {
        $data = [
            'email' => 'test@example.com',
            'location' => 'London',
            'notification_time' => 'invalid-time'
        ];

        $response = $this->postJson('/api/subscriptions', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notification_time']);
    }

    public function test_store_fails_with_invalid_location(): void
    {
        $data = [
            'email' => 'test@example.com',
            'location' => 'NonExistentCity',
            'notification_time' => '09:00'
        ];

        $this->weatherService
            ->shouldReceive('getWeatherData')
            ->once()
            ->andThrow(new \Exception('Location not found'));

        $response = $this->postJson('/api/subscriptions', $data);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Failed to create subscription'
            ]);
    }

    public function test_show_returns_subscription(): void
    {
        $subscription = Subscription::factory()->create();

        $response = $this->getJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $subscription->id,
                'email' => $subscription->email,
                'location' => $subscription->location
            ]);
    }

    public function test_show_returns_404_for_nonexistent_subscription(): void
    {
        $response = $this->getJson('/api/subscriptions/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_subscription(): void
    {
        $subscription = Subscription::factory()->create();
        $data = [
            'notification_time' => '10:00'
        ];

        $this->weatherService
            ->shouldReceive('getWeatherData')
            ->never();

        $response = $this->putJson("/api/subscriptions/{$subscription->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscription updated successfully',
                'data' => [
                    'id' => $subscription->id,
                    'email' => $subscription->email,
                    'location' => $subscription->location
                ]
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'notification_time' => $data['notification_time']
        ]);
    }

    public function test_update_validates_location_when_changed(): void
    {
        $subscription = Subscription::factory()->create();
        $data = [
            'location' => 'Paris'
        ];

        $mockWeatherData = WeatherResponseDTO::fromArray([
            'location' => [
                'name' => 'Paris',
                'country' => 'France',
                'region' => 'Ile-de-France',
                'lat' => '48.8567',
                'lon' => '2.3508',
                'timezone_id' => 'Europe/Paris',
                'localtime' => '2024-01-20 10:00',
                'localtime_epoch' => 1705744800,
                'utc_offset' => '1.0'
            ],
            'current' => [
                'observation_time' => '10:00 AM',
                'temperature' => 18,
                'weather_code' => 113,
                'weather_icons' => ['https://example.com/clear.png'],
                'weather_descriptions' => ['Clear'],
                'wind_speed' => 8,
                'wind_degree' => 225,
                'wind_dir' => 'SW',
                'pressure' => 1012,
                'precip' => 0,
                'humidity' => 65,
                'cloudcover' => 10,
                'feelslike' => 17,
                'uv_index' => 4,
                'visibility' => 10,
                'is_day' => 'yes'
            ]
        ]);

        $this->weatherService
            ->shouldReceive('getWeatherData')
            ->once()
            ->andReturn($mockWeatherData);

        $response = $this->putJson("/api/subscriptions/{$subscription->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscription updated successfully',
                'data' => [
                    'id' => $subscription->id,
                    'location' => 'Paris'
                ]
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'location' => 'Paris'
        ]);
    }

    public function test_destroy_deletes_subscription(): void
    {
        $subscription = Subscription::factory()->create();

        $response = $this->deleteJson("/api/subscriptions/{$subscription->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscription deleted successfully'
            ]);

        $this->assertSoftDeleted($subscription);
    }

    public function test_unsubscribe_deactivates_subscription(): void
    {
        $subscription = Subscription::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/subscriptions/unsubscribe/{$subscription->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully unsubscribed from weather notifications'
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'is_active' => false
        ]);
    }

    public function test_unsubscribe_handles_already_inactive_subscription(): void
    {
        $subscription = Subscription::factory()->create(['is_active' => false]);

        $response = $this->postJson("/api/subscriptions/unsubscribe/{$subscription->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscription is already inactive'
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 