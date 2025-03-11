<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\WeatherService;
use App\DTOs\Weather\WeatherRequestDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWeatherNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Subscription $subscription
    ) {}

    public function handle(WeatherService $weatherService): void
    {
        $weatherRequest = new WeatherRequestDTO($this->subscription->location);
        $weatherData = $weatherService->getWeatherData($weatherRequest);
        
        if (!$weatherData) {
            return;
        }

        $data = $weatherData->toArray();
        $data['subscription_id'] = $this->subscription->id;

        Mail::send(
            'emails.weather_forecast',
            ['weatherData' => $data],
            function ($message) {
                $message->to($this->subscription->email)
                    ->subject('Your Weather Forecast');
            }
        );

        $this->subscription->update([
            'last_notification_sent_at' => now(),
        ]);
    }
} 