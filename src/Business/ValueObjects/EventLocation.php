<?php

namespace Business\ValueObjects;

use Business\Common\Guards\StringGuard;
use Business\Exceptions\ValidationException;

class EventLocation extends GeometricCoordinates
{
    public const MAX_ADDRESS_DETAILS_LENGTH = 100;

    /**
     * @throws ValidationException
     */
    public function __construct(
        public readonly string $address,
        public readonly string $postalCode,
        public readonly string $city,
        public readonly string $country,
        public readonly string $addressDetails,
        float                  $latitude,
        float                  $longitude
    )
    {
        StringGuard::from($this->addressDetails)->isNotLongerThan(self::MAX_ADDRESS_DETAILS_LENGTH, 'too long location details');
        parent::__construct($latitude, $longitude);
    }
}
