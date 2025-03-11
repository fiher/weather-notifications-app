<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WeatherSubscriptionCreated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Weather Subscription Confirmation')
            ->greeting('Hello!')
            ->line('Thank you for subscribing to our weather notification service.')
            ->line('Your subscription details:')
            ->line("Location: {$this->subscription->location}")
            ->line("Notification Time: {$this->subscription->notification_time}")
            ->line('You will receive daily weather updates at your specified time.')
            ->action('View Subscription', url("/subscriptions/{$this->subscription->id}"))
            ->line('If you wish to unsubscribe, you can do so at any time.');
    }
} 