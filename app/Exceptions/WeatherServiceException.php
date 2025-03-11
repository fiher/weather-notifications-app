<?php

namespace App\Exceptions;

use Exception;

class WeatherServiceException extends Exception
{
    private array $context;

    public function __construct(string $message, array $context = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function apiError(string $location, array $error): self
    {
        return new self(
            "WeatherStack API error for location: {$location}",
            ['location' => $location, 'error' => $error]
        );
    }

    public static function dataNotAvailable(string $location): self
    {
        return new self(
            "Weather data not available for location: {$location}",
            ['location' => $location]
        );
    }

    public static function invalidResponse(string $location, int $status): self
    {
        return new self(
            "Invalid API response for location: {$location}",
            ['location' => $location, 'status' => $status]
        );
    }
} 