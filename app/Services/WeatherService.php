<?php

namespace App\Services;

use App\DTOs\Weather\WeatherRequestDTO;
use App\DTOs\Weather\WeatherResponseDTO;
use App\Exceptions\WeatherServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private int $retryAttempts;
    private int $retryDelay;
    private int $cacheTtl;

    public function __construct()
    {
        $this->apiKey = config('services.weatherstack.key');
        $this->baseUrl = config('services.weatherstack.url');
        $this->timeout = config('services.weatherstack.timeout', 15);
        $this->retryAttempts = config('services.weatherstack.retry_attempts', 3);
        $this->retryDelay = config('services.weatherstack.retry_delay', 1000);
        $this->cacheTtl = config('services.weatherstack.cache_ttl', 30);
    }

    public function getWeatherData(WeatherRequestDTO $request): WeatherResponseDTO
    {
        $cacheKey = "weather:{$request->location}";

        try {
            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($request) {
                $response = Http::timeout($this->timeout)
                    ->retry($this->retryAttempts, $this->retryDelay)
                    ->get($this->baseUrl . '/current', [
                        'access_key' => $this->apiKey,
                        'query' => $request->location,
                        'units' => $request->units
                    ]);

                if (!$response->successful()) {
                    throw WeatherServiceException::invalidResponse(
                        $request->location,
                        $response->status()
                    );
                }

                $data = $response->json();
                
                if (isset($data['error'])) {
                    throw WeatherServiceException::apiError($request->location, $data['error']);
                }

                if (!isset($data['current']) || !isset($data['location'])) {
                    throw WeatherServiceException::dataNotAvailable($request->location);
                }

                $weatherResponse = WeatherResponseDTO::fromArray($data);

                Log::channel('weather')->info('Weather data retrieved', [
                    'location' => $request->location,
                    'temperature' => $weatherResponse->current->temperature,
                    'condition' => $weatherResponse->current->getPrimaryWeatherDescription()
                ]);

                return $weatherResponse;
            });
        } catch (Exception $e) {
            Log::channel('weather')->error('Weather service error', [
                'location' => $request->location,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof WeatherServiceException) {
                throw $e;
            }

            throw new WeatherServiceException(
                "Failed to fetch weather data for location: {$request->location}",
                ['location' => $request->location],
                0,
                $e
            );
        }
    }

    public function isAvailable(): bool
    {
        try {
            $request = new WeatherRequestDTO('London');
            $response = Http::timeout(5)
                ->get($this->baseUrl . '/current', [
                    'access_key' => $this->apiKey,
                    'query' => $request->location,
                    'units' => $request->units
                ]);

            return $response->successful() && !isset($response->json()['error']);
        } catch (Exception $e) {
            Log::channel('weather')->error('Health check failed', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
} 