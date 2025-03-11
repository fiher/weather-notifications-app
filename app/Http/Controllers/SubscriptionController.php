<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Interfaces\SubscriptionServiceInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Services\WeatherService;
use App\DTOs\Weather\WeatherRequestDTO;
use App\Notifications\WeatherSubscriptionCreated;
use App\Notifications\WeatherSubscriptionCancelled;
use App\Notifications\WeatherUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly WeatherService $weatherService
    ) {}

    public function index(): JsonResponse
    {
        $subscriptions = Subscription::where('is_active', true)->get();
        return response()->json($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'location' => 'required|string|min:2|max:100',
            'notification_time' => 'required|date_format:H:i'
        ]);

        try {
            $weatherRequest = new WeatherRequestDTO($validated['location']);
            $weatherData = $this->weatherService->getWeatherData($weatherRequest);

            $subscription = Subscription::create([
                'email' => $validated['email'],
                'location' => $validated['location'],
                'notification_time' => $validated['notification_time'],
                'is_active' => true
            ]);

            $subscription->notify(new WeatherSubscriptionCreated($subscription));

            $subscription->notify(new WeatherUpdate($subscription, $weatherData));

            return response()->json([
                'message' => 'Subscription created successfully',
                'data' => $subscription
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create subscription', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json([
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $subscription = Subscription::findOrFail($id);
        return response()->json($subscription);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $subscription = Subscription::findOrFail($id);

            $validated = $request->validate([
                'email' => 'sometimes|email',
                'location' => 'sometimes|string|min:2|max:100',
                'notification_time' => 'sometimes|date_format:H:i'
            ]);

            if (isset($validated['location'])) {
                $weatherRequest = new WeatherRequestDTO($validated['location']);
                $this->weatherService->getWeatherData($weatherRequest);
            }

            $subscription->update($validated);

            return response()->json([
                'message' => 'Subscription updated successfully',
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $id,
                'data' => []
            ]);

            return response()->json([
                'message' => 'Failed to update subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->delete();

            return response()->json([
                'message' => 'Subscription deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $id
            ]);

            return response()->json([
                'message' => 'Failed to delete subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unsubscribe(string $id): JsonResponse
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            if (!$subscription->is_active) {
                return response()->json([
                    'message' => 'Subscription is already inactive'
                ]);
            }

            $subscription->is_active = false;
            $subscription->save();

            $subscription->notify(new WeatherSubscriptionCancelled($subscription));

            return response()->json([
                'message' => 'Successfully unsubscribed from weather notifications'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unsubscribe', [
                'error' => $e->getMessage(),
                'subscription_id' => $id
            ]);

            return response()->json([
                'message' => 'Failed to process unsubscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 