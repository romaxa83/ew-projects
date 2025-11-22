<?php

namespace App\Rules\Catalog\Solution;

use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use Illuminate\Contracts\Validation\Rule;

class BtuRule implements Rule
{
    public function __construct(private array $args, private ?string $type = SolutionTypeEnum::OUTDOOR)
    {
    }

    public function passes($attribute, $value): bool
    {
        if ($this->type === SolutionTypeEnum::OUTDOOR) {
            return $this->passesOutdoor($value);
        }

        if ($this->type === SolutionTypeEnum::INDOOR) {
            return $this->passesIndoor($value);
        }

        return $this->passesCreateSolution();
    }

    public function passesOutdoor(string $value): bool
    {
        $availableBTUs = config(
            'catalog.solutions.btu.lists.' .
            $this->type .
            '.' .
            $this->args['zone']
        );
        return in_array((int)$value, $availableBTUs, true);
    }

    public function passesIndoor(array $indoors): bool
    {
        $zone = $this->args['count_zones'] !== 1 ? SolutionZoneEnum::MULTI : SolutionZoneEnum::SINGLE;

        foreach ($indoors as $indoor) {
            $availableBTUs = config(
                'catalog.solutions.btu.lists.' .
                $this->type .
                '.' .
                $zone .
                '.' .
                $indoor['type']
            );

            if (!in_array($indoor['btu'], $availableBTUs, true)) {
                return false;
            }
        }
        return true;
    }

    public function passesCreateSolution(): bool
    {
        if ($this->args['type'] === SolutionTypeEnum::LINE_SET) {
            return true;
        }
        if ($this->args['type'] === SolutionTypeEnum::OUTDOOR) {
            $availableBTUs = config(
                'catalog.solutions.btu.lists.' .
                $this->args['type'] .
                '.' .
                $this->args['zone']
            );
        } else {
            $availableBTUs = array_merge(
                config(
                    'catalog.solutions.btu.lists.' .
                    $this->args['type'] .
                    '.' .
                    SolutionZoneEnum::SINGLE .
                    '.' .
                    $this->args['indoor_type']
                ),
                config(
                    'catalog.solutions.btu.lists.' .
                    $this->args['type'] .
                    '.' .
                    SolutionZoneEnum::MULTI .
                    '.' .
                    $this->args['indoor_type']
                )
            );
        }
        return in_array((int)$this->args['btu'], $availableBTUs, true);
    }

    public function message(): string
    {
        return __('validation.custom.catalog.solutions.incorrect_btu');
    }
}
