<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\DTOs\Weather\WeatherResponseDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WeatherUpdate extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription,
        private readonly WeatherResponseDTO $weatherData
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $current = $this->weatherData->current;
        $location = $this->weatherData->location;

        return (new MailMessage)
            ->subject("Weather Update for {$location->name}")
            ->greeting('Hello!')
            ->line("Here's your daily weather update for {$location->name}, {$location->country}:")
            ->line("Temperature: {$current->temperature}°C")
            ->line("Condition: {$current->getPrimaryWeatherDescription()}")
            ->line("Wind: {$current->wind_speed} km/h {$current->wind_dir}")
            ->line("Humidity: {$current->humidity}%")
            ->line("Feels Like: {$current->feelslike}°C")
            ->action('View Full Forecast', url("/weather?location={$location->name}"))
            ->line('Thank you for using our weather service!');
    }
} 