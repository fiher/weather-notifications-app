<?php

namespace App\Http\Controllers;

use App\DTOs\Weather\WeatherRequestDTO;
use App\Exceptions\WeatherServiceException;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class WeatherController extends Controller
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function current(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'location' => 'required|string|min:2|max:100',
                'units' => 'sometimes|string|in:m,f,s',
                'language' => 'sometimes|string|size:2'
            ]);

            $weatherRequest = new WeatherRequestDTO(
                location: $request->location,
                units: $request->input('units', 'm'),
                language: $request->input('language', 'en')
            );

            $weatherData = $this->weatherService->getWeatherData($weatherRequest);
            
            return response()->json($weatherData->toArray());
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ], 422);
        } catch (WeatherServiceException $e) {
            return response()->json([
                'error' => 'Weather service error',
                'message' => $e->getMessage(),
                'context' => $e->getContext()
            ], 500);
        }
    }

    public function health(): JsonResponse
    {
        $isAvailable = $this->weatherService->isAvailable();
        
        return response()->json([
            'status' => $isAvailable ? 'available' : 'unavailable'
        ], $isAvailable ? 200 : 503);
    }
} 