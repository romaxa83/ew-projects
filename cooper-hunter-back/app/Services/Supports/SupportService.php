<?php

declare(strict_types=1);

namespace App\Services\Supports;

use App\Dto\Supports\SupportDto;
use App\Models\Support\Supports\Support;

class SupportService
{
    public function createOrUpdate(SupportDto $dto): Support
    {
        $support = Support::firstOrNew();

        return $this->store($support, $dto);
    }

    protected function store(Support $support, SupportDto $dto): Support
    {
        $this->fill($dto, $support);

        $support->save();

        $this->saveTranslations($support, $dto);

        return $support;
    }

    protected function fill(SupportDto $dto, Support $support): void
    {
        $support->phone = $dto->getPhone();
    }

    protected function saveTranslations(Support $support, SupportDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $support->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'description' => $translation->getDescription(),
                    'short_description' => $translation->getShortDescription(),
                    'working_time' => $translation->getWorkingTime(),
                    'video_link' => $translation->getVideoLink(),
                ]
            );
        }
    }
}
