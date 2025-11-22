<?php

declare(strict_types=1);

namespace App\Services\Sliders;

use App\Dto\Sliders\SliderDto;
use App\Models\Sliders\Slider;

class SliderService
{
    public function create(SliderDto $dto): Slider
    {
        $slider = new Slider();

        return $this->store($slider, $dto);
    }

    protected function store(Slider $slider, SliderDto $dto): Slider
    {
        $this->fill($dto, $slider);

        $slider->save();

        $this->saveTranslations($slider, $dto);

        return $slider;
    }

    protected function fill(SliderDto $dto, Slider $slider): void
    {
        $slider->active = $dto->getActive();
        $slider->link = $dto->getLink();
    }

    protected function saveTranslations(Slider $slider, SliderDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $slider->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                ]
            );
        }
    }

    public function update(Slider $slider, SliderDto $dto): Slider
    {
        return $this->store($slider, $dto);
    }

    public function toggle(Slider $slider): Slider
    {
        $slider->active = !$slider->active;
        $slider->save();

        return $slider;
    }

    public function delete(Slider $slider): bool
    {
        return $slider->delete();
    }
}
