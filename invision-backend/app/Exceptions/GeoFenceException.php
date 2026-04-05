<?php

namespace App\Exceptions;

use Exception;

class GeoFenceException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?float $distanceMeters = null,
        public readonly ?int $radiusMeters = null,
    ) {
        parent::__construct($message);
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'distance_meters' => $this->distanceMeters,
            'radius_meters' => $this->radiusMeters,
        ];
    }
}
