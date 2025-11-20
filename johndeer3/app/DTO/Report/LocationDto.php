<?php

namespace App\DTO\Report;

class LocationDto
{
    public $lat;
    public $long;
    public $country;
    public $city;
    public $region;
    public $zipcode;
    public $street;
    public $district;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->lat = $args['location_lat'] ?? null;
        $self->long = $args['location_long'] ?? null;
        $self->country = $args['location_country'] ?? null;
        $self->city = $args['location_city'] ?? null;
        $self->region = $args['location_region'] ?? null;
        $self->zipcode = $args['location_zipcode'] ?? null;
        $self->street = $args['location_street'] ?? null;
        $self->district = $args['location_district'] ?? null;

        return $self;
    }
}

