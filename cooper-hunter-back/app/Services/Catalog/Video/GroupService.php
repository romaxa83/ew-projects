<?php

namespace App\Services\Catalog\Video;

use App\Dto\Catalog\Video\GroupDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Videos\Group;
use App\Traits\Model\ToggleActive;
use Exception;
use Illuminate\Support\Str;
use Throwable;

class GroupService
{
    use ToggleActive;

    private function saveTranslations(array $translations, Group $group)
    {
        array_map(
            fn(SimpleTranslationDto $dto) => $group->translations()
                ->updateOrCreate(
                    [
                        'language' => $dto->getLanguage()
                    ],
                    [
                        'language' => $dto->getLanguage(),
                        'description' => $dto->getDescription(),
                        'title' => $dto->getTitle(),
                        'slug' => Str::slug($dto->getTitle())
                    ]
                ),
            $translations
        );
    }

    private function modifyModel(GroupDto $dto, Group $group): Group
    {
        $group->active = $dto->getActive();
        $group->save();

        $this->saveTranslations($dto->getTranslations(), $group);

        return $group->refresh();
    }

    public function create(GroupDto $dto): Group
    {
        return $this->modifyModel($dto, new Group());
    }

    public function update(GroupDto $dto, Group $group): Group
    {
        return $this->modifyModel($dto, $group);
    }

    public function remove(Group $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}


