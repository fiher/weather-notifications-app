<?php

namespace Tests\Unit\Services;

use App\DTOs\Weather\WeatherRequestDTO;
use App\Exceptions\WeatherServiceException;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class WeatherServiceTest extends TestCase
{
    private WeatherService $weatherService;
    private string $apiKey = 'test_api_key';
    private string $baseUrl = 'http://api.weatherstack.com';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.weatherstack.key', $this->apiKey);
        Config::set('services.weatherstack.url', $this->baseUrl);
        Config::set('services.weatherstack.timeout', 15);
        Config::set('services.weatherstack.retry_attempts', 3);
        Config::set('services.weatherstack.retry_delay', 1000);
        Config::set('services.weatherstack.cache_ttl', 30);

        $this->weatherService = new WeatherService();
    }

    public function test_get_weather_data_success(): void
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

        Http::fake([
            "{$this->baseUrl}/current*" => Http::response($mockResponse, 200)
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        Log::shouldReceive('channel')
            ->once()
            ->with('weather')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $request = new WeatherRequestDTO($location);
        $response = $this->weatherService->getWeatherData($request);

        $this->assertEquals($location, $response->location->name);
        $this->assertEquals(15, $response->current->temperature);
        $this->assertEquals('Partly cloudy', $response->current->getPrimaryWeatherDescription());
        $this->assertEquals(10, $response->current->wind_speed);
        $this->assertEquals('NE', $response->current->wind_dir);
        $this->assertEquals(75, $response->current->humidity);
        $this->assertEquals(14, $response->current->feelslike);
    }

    public function test_get_weather_data_api_error(): void
    {
        $location = 'Invalid Location';
        $errorResponse = [
            'error' => [
                'code' => 615,
                'type' => 'request_failed',
                'info' => 'Location not found'
            ]
        ];

        Http::fake([
            "{$this->baseUrl}/current*" => Http::response($errorResponse, 200)
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        Log::shouldReceive('channel')
            ->once()
            ->with('weather')
            ->andReturnSelf()
            ->shouldReceive('error')
            ->once();

        $this->expectException(WeatherServiceException::class);
        $this->expectExceptionMessage("WeatherStack API error for location: {$location}");

        $request = new WeatherRequestDTO($location);
        $this->weatherService->getWeatherData($request);
    }

    public function test_get_weather_data_invalid_response(): void
    {
        $location = 'London';
        $invalidResponse = [
            'some' => 'invalid data'
        ];

        Http::fake([
            "{$this->baseUrl}/current*" => Http::response($invalidResponse, 200)
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        Log::shouldReceive('channel')
            ->once()
            ->with('weather')
            ->andReturnSelf()
            ->shouldReceive('error')
            ->once();

        $this->expectException(WeatherServiceException::class);
        $this->expectExceptionMessage("Weather data not available for location: {$location}");

        $request = new WeatherRequestDTO($location);
        $this->weatherService->getWeatherData($request);
    }

    public function test_get_weather_data_http_error(): void
    {
        $location = 'London';

        Http::fake([
            "{$this->baseUrl}/current*" => Http::response(null, 500)
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        Log::shouldReceive('channel')
            ->once()
            ->with('weather')
            ->andReturnSelf()
            ->shouldReceive('error')
            ->once();

        $this->expectException(WeatherServiceException::class);
        $this->expectExceptionMessage("Failed to fetch weather data for location: {$location}");

        $request = new WeatherRequestDTO($location);
        $this->weatherService->getWeatherData($request);
    }

    public function test_is_available_returns_true_when_api_is_up(): void
    {
        Http::fake([
            "{$this->baseUrl}/current*" => Http::response(['current' => [
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
            ]], 200)
        ]);

        Log::shouldReceive('channel')
            ->never()
            ->with('weather');

        $this->assertTrue($this->weatherService->isAvailable());
    }

    public function test_is_available_returns_false_when_api_is_down(): void
    {
        Http::fake([
            "{$this->baseUrl}/current*" => function() {
                throw new \Exception('Connection failed');
            }
        ]);

        Log::shouldReceive('channel')
            ->once()
            ->with('weather')
            ->andReturnSelf()
            ->shouldReceive('error')
            ->once()
            ->with('Health check failed', ['message' => 'Connection failed']);

        $this->assertFalse($this->weatherService->isAvailable());
    }
} 