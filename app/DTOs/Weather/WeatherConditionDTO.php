<?php

namespace App\DTOs\Weather;

class WeatherConditionDTO
{
    public function __construct(
        public readonly string $observation_time,
        public readonly int $temperature,
        public readonly int $weather_code,
        public readonly array $weather_icons,
        public readonly array $weather_descriptions,
        public readonly int $wind_speed,
        public readonly int $wind_degree,
        public readonly string $wind_dir,
        public readonly int $pressure,
        public readonly int $precip,
        public readonly int $humidity,
        public readonly int $cloudcover,
        public readonly int $feelslike,
        public readonly int $uv_index,
        public readonly int $visibility,
        public readonly string $is_day
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            observation_time: $data['observation_time'],
            temperature: $data['temperature'],
            weather_code: $data['weather_code'],
            weather_icons: $data['weather_icons'],
            weather_descriptions: $data['weather_descriptions'],
            wind_speed: $data['wind_speed'],
            wind_degree: $data['wind_degree'],
            wind_dir: $data['wind_dir'],
            pressure: $data['pressure'],
            precip: $data['precip'],
            humidity: $data['humidity'],
            cloudcover: $data['cloudcover'],
            feelslike: $data['feelslike'],
            uv_index: $data['uv_index'],
            visibility: $data['visibility'],
            is_day: $data['is_day']
        );
    }

    public function toArray(): array
    {
        return [
            'observation_time' => $this->observation_time,
            'temperature' => $this->temperature,
            'weather_code' => $this->weather_code,
            'weather_icons' => $this->weather_icons,
            'weather_descriptions' => $this->weather_descriptions,
            'wind_speed' => $this->wind_speed,
            'wind_degree' => $this->wind_degree,
            'wind_dir' => $this->wind_dir,
            'pressure' => $this->pressure,
            'precip' => $this->precip,
            'humidity' => $this->humidity,
            'cloudcover' => $this->cloudcover,
            'feelslike' => $this->feelslike,
            'uv_index' => $this->uv_index,
            'visibility' => $this->visibility,
            'is_day' => $this->is_day
        ];
    }

    public function getPrimaryWeatherDescription(): string
    {
        return $this->weather_descriptions[0] ?? 'Unknown';
    }
} 