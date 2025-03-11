<?php

namespace App\DTOs\Weather;

class WeatherResponseDTO
{
    public function __construct(
        public readonly WeatherLocationDTO $location,
        public readonly WeatherConditionDTO $current
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            location: WeatherLocationDTO::fromArray($data['location']),
            current: WeatherConditionDTO::fromArray($data['current'])
        );
    }

    public function toArray(): array
    {
        return [
            'location' => $this->location->toArray(),
            'current' => $this->current->toArray()
        ];
    }
} 