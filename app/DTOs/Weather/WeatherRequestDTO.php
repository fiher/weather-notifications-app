<?php

namespace App\DTOs\Weather;

use InvalidArgumentException;

class WeatherRequestDTO
{
    private const ALLOWED_UNITS = ['m', 'f', 's'];
    private const ALLOWED_LANGUAGES = ['ar', 'bn', 'bg', 'zh', 'cs', 'da', 'nl', 'fi', 'fr', 'de', 'el', 'hi', 'hu', 'it', 'ja', 'jv', 'ko', 'zh_tw', 'mr', 'pl', 'pt', 'pa', 'ro', 'ru', 'sr', 'si', 'sk', 'es', 'sv', 'ta', 'te', 'tr', 'uk', 'ur', 'vi', 'en'];

    public function __construct(
        public readonly string $location,
        public readonly string $units = 'm',
        public readonly string $language = 'en'
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->location)) {
            throw new InvalidArgumentException('Location cannot be empty');
        }

        if (!in_array($this->units, self::ALLOWED_UNITS)) {
            throw new InvalidArgumentException(
                sprintf('Invalid units. Allowed values: %s', implode(', ', self::ALLOWED_UNITS))
            );
        }

        if (!in_array($this->language, self::ALLOWED_LANGUAGES)) {
            throw new InvalidArgumentException(
                sprintf('Invalid language. Must be a 2-letter ISO code. Allowed values: %s', implode(', ', self::ALLOWED_LANGUAGES))
            );
        }
    }

    public function toArray(): array
    {
        return [
            'query' => $this->location,
            'units' => $this->units,
            'language' => $this->language
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            location: $data['location'] ?? $data['query'] ?? '',
            units: $data['units'] ?? 'm',
            language: $data['language'] ?? 'en'
        );
    }
} 