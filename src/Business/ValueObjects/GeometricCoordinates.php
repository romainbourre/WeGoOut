<?php

namespace Business\ValueObjects;

class GeometricCoordinates
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly float $altitude = 0
    ) {
    }

    public function getDistance(GeometricCoordinates $coordinates): float
    {
        $earthRayon = 6371;
        $originLatitudeRadian = deg2rad($this->latitude);
        $destinationLatitudeRadian = deg2rad($coordinates->latitude);
        $originLongitudeRadian = deg2rad($this->longitude);
        $destinationLongitudeRadian = deg2rad($coordinates->longitude);
        $originKilometersAltitude = $this->altitude / 1000;
        $destinationKilometersAltitude = $coordinates->altitude / 1000;

        //calcul précis
        $dp = 2 * asin(
                sqrt(
                    pow(sin(($originLatitudeRadian - $destinationLatitudeRadian) / 2), 2) + cos(
                        $originLatitudeRadian
                    ) * cos($destinationLatitudeRadian) * pow(
                        sin(($originLongitudeRadian - $destinationLongitudeRadian) / 2),
                        2
                    )
                )
            );

        //sortie en km
        $d = $dp * $earthRayon;

        //Pythagore a dit que :
        $h = sqrt(pow($d, 2) + pow($destinationKilometersAltitude - $originKilometersAltitude, 2));

        //On remet le résultat en kilomètre
        return $h * 1000;
    }
}