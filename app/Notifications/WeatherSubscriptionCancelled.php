<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WeatherSubscriptionCancelled extends Notification
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
            ->subject('Weather Subscription Cancelled')
            ->greeting('Hello!')
            ->line('Your weather notification subscription has been cancelled successfully.')
            ->line('Subscription details:')
            ->line("Location: {$this->subscription->location}")
            ->line("Notification Time: {$this->subscription->notification_time}")
            ->line('We hope you found our service useful.')
            ->line('You can resubscribe at any time if you change your mind.')
            ->action('Subscribe Again', url('/subscriptions/create'));
    }
} 