<?php

namespace App\ValueObjects;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Point
{
    public function __construct(private float $longitude, private float $latitude)
    {
        if (-180 > $this->longitude || $this->longitude > 180) {
            throw new InvalidArgumentException(
                "The longitude must be in range between -180 and 180. '$longitude' given"
            );
        }

        if (-90 > $this->latitude || $this->latitude > 90) {
            throw new InvalidArgumentException("The latitude must be in range between -90 and 90. '$latitude' given");
        }
    }

    /**
     * Decode from database Point field value
     */
    public static function decode(string $value): self
    {
        $coordinates = unpack('x/x/x/x/corder/Ltype/dlongitude/dlatitude', $value);

        return self::byCoordinates($coordinates);
    }

    public static function byCoordinates(array $coordinates): self
    {
        return new self($coordinates['longitude'], $coordinates['latitude']);
    }

    /**
     * Mysql way to get 'self::encode()' Point value
     * @see Point::encode()
     */
    public function getGeoPoint(): Expression
    {
        return DB::raw("ST_GeomFromText('POINT($this->longitude $this->latitude)')");
    }

    /**
     * Encode to database Point field value
     */
    public function encode(): string
    {
        return pack('xxxxcLdd', '0', 1, $this->getLatitude(), $this->getLongitude());
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function asCoordinatesWithRadius(int $radius): array
    {
        $radius = (int)abs($radius);

        return array_merge(
            $this->asCoordinates(),
            compact('radius')
        );
    }

    public function asCoordinates(): array
    {
        return [
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
        ];
    }
}
