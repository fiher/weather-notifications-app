<?php

namespace App\Console;

use App\Jobs\SendWeatherNotification;
use App\Models\Subscription;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $subscriptions = Subscription::where('is_active', true)->get();

        foreach ($subscriptions as $subscription) {
            $schedule->job(new SendWeatherNotification($subscription))
                ->dailyAt($subscription->notification_time->format('H:i'))
                ->onSuccess(function () use ($subscription) {
                    info("Weather notification sent successfully for subscription {$subscription->id}");
                })
                ->onFailure(function () use ($subscription) {
                    error_log("Failed to send weather notification for subscription {$subscription->id}");
                });
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
} 