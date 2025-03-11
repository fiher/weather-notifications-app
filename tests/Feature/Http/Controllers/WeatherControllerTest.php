<?php

namespace Tests\Feature\Http\Controllers;

use App\DTOs\Weather\WeatherRequestDTO;
use App\DTOs\Weather\WeatherResponseDTO;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase;

    private WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherService = Mockery::mock(WeatherService::class);
        $this->app->instance(WeatherService::class, $this->weatherService);
    }

    public function test_get_weather_returns_weather_data(): void
    {
        $location = 'London';
        $mockResponse = [
            'location' => [
                'name' => 'London',
                'country' => 'United Kingdom',
                'region' => 'City of London, Greater London',
                'lat' => '51.517',
                'lon' => '-0.106',
                'timezone_id' => 'Europe/London',
                'localtime' => '2024-03-14 10:45',
                'localtime_epoch' => 1710408300,
                'utc_offset' => '0.0'
            ],
            'current' => [
                'observation_time' => '10:45 AM',
                'temperature' => 15,
                'weather_code' => 116,
                'weather_icons' => ['https://example.com/icon.png'],
                'weather_descriptions' => ['Partly cloudy'],
                'wind_speed' => 10,
                'wind_degree' => 220,
                'wind_dir' => 'NE',
                'pressure' => 1015,
                'precip' => 0,
                'humidity' => 75,
                'cloudcover' => 25,
                'feelslike' => 14,
                'uv_index' => 4,
                'visibility' => 10,
                'is_day' => 'yes'
            ]
        ];

        $this->weatherService
            ->shouldReceive('getWeatherData')
            ->once()
            ->with(Mockery::type(WeatherRequestDTO::class))
            ->andReturn(WeatherResponseDTO::fromArray($mockResponse));

        $response = $this->getJson("/api/weather/current?location={$location}");

        $response->assertStatus(200)
            ->assertJson([
                'location' => [
                    'name' => 'London',
                    'country' => 'United Kingdom'
                ],
                'current' => [
                    'temperature' => 15,
                    'weather_descriptions' => ['Partly cloudy']
                ]
            ]);
    }

    public function test_get_weather_returns_error_for_invalid_location(): void
    {
        $response = $this->getJson("/api/weather/current");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The location field is required.',
                'errors' => [
                    'location' => ['The location field is required.']
                ]
            ]);
    }

    public function test_health_check_returns_success(): void
    {
        $this->weatherService
            ->shouldReceive('isAvailable')
            ->once()
            ->andReturn(true);

        $response = $this->getJson('/api/weather/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'available'
            ]);
    }

    public function test_health_check_returns_error(): void
    {
        $this->weatherService
            ->shouldReceive('isAvailable')
            ->once()
            ->andReturn(false);

        $response = $this->getJson('/api/weather/health');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'unavailable'
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 