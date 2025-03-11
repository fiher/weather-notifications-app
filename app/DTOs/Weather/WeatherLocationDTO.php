<?php

namespace App\DTOs\Weather;

class WeatherLocationDTO
{
    public readonly string $name;
    public readonly string $country;
    public readonly string $region;
    public readonly string $lat;
    public readonly string $lon;
    public readonly string $timezone_id;
    public readonly ?string $localtime;
    public readonly ?int $localtime_epoch;
    public readonly ?string $utc_offset;

    public function __construct(
        string $name,
        string $country,
        string $region,
        string $lat,
        string $lon,
        string $timezone_id,
        ?string $localtime = null,
        ?int $localtime_epoch = null,
        ?string $utc_offset = null
    ) {
        $this->name = $name;
        $this->country = $country;
        $this->region = $region;
        $this->lat = $lat;
        $this->lon = $lon;
        $this->timezone_id = $timezone_id;
        $this->localtime = $localtime;
        $this->localtime_epoch = $localtime_epoch;
        $this->utc_offset = $utc_offset;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            country: $data['country'],
            region: $data['region'],
            lat: $data['lat'],
            lon: $data['lon'],
            timezone_id: $data['timezone_id'],
            localtime: $data['localtime'] ?? null,
            localtime_epoch: $data['localtime_epoch'] ?? null,
            utc_offset: $data['utc_offset'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'country' => $this->country,
            'region' => $this->region,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'timezone_id' => $this->timezone_id,
            'localtime' => $this->localtime,
            'localtime_epoch' => $this->localtime_epoch,
            'utc_offset' => $this->utc_offset
        ];
    }
} 