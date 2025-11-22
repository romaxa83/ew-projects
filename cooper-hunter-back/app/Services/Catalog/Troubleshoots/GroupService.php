<?php

namespace App\Services\Catalog\Troubleshoots;

use App\Dto\Catalog\Troubleshoots\GroupDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Troubleshoots\Group;
use App\Traits\Model\ToggleActive;
use Exception;
use Illuminate\Support\Str;
use Throwable;

class GroupService
{
    use ToggleActive;

    private function saveTranslations(Group $group, array $translations): void
    {
        array_map(
            fn(SimpleTranslationDto $dto) => $group->translations()
                ->updateOrCreate(
                    [
                        'language' => $dto->getLanguage()
                    ],
                    [
                        'language' => $dto->getLanguage(),
                        'title' => $dto->getTitle(),
                        'slug' => Str::slug($dto->getTitle()),
                        'description' => $dto->getDescription()
                    ]
                ),
            $translations
        );
    }

    private function modifyModel(GroupDto $dto, Group $group): Group
    {
        $group->active = $dto->getActive();
        $group->save();

        $this->saveTranslations($group, $dto->getTranslations());

        return $group->refresh();
    }

    public function create(GroupDto $dto): Group
    {
        return $this->modifyModel($dto, new Group());
    }

    public function update(GroupDto $dto, Group $model): Group
    {
        return $this->modifyModel($dto, $model);
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


