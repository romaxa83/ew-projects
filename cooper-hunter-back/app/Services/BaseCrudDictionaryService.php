<?php

namespace App\Services;

use App\Contracts\Roles\HasGuardUser;
use App\Dto\BaseDictionaryDto;
use App\Dto\BaseTranslationDto;
use App\Models\BaseHasTranslation;
use Core\Exceptions\TranslatedException;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class BaseCrudDictionaryService
 * @package App\Services
 */
abstract class BaseCrudDictionaryService
{
    public function create(BaseDictionaryDto $dto): BaseHasTranslation
    {
        return $this->modifyDictionaryModel(
            $this->getModelInstance(),
            $dto
        );
    }

    private function modifyDictionaryModel(BaseHasTranslation $model, BaseDictionaryDto $dto): BaseHasTranslation
    {
        $model->active = $dto->getActive();

        if (($guid = $dto->getModelGuid()) && in_array('guid', $model->getFillable(), true)) {
            $model->guid = $guid;
        }

        $model->save();

        $this->saveTranslations($model, $dto->getTranslations());

        return $model;
    }

    /**
     * @param BaseHasTranslation $model
     * @param BaseTranslationDto[] $translations
     */
    private function saveTranslations(BaseHasTranslation $model, array $translations): void
    {
        foreach ($translations as $translation) {
            $model->translations()
                ->updateOrCreate(
                    [
                        'language' => $translation->getLanguage(),
                    ],
                    [
                        'slug' => $translation->getSlug(),
                        'title' => $translation->getTitle(),
                        'description' => $translation->getDescription()
                    ]
                );
        }
    }

    protected function getModelInstance(): BaseHasTranslation
    {
        $model = $this->getModel();

        return new $model();
    }

    abstract protected function getModel(): string;

    public function update(BaseDictionaryDto $dto): BaseHasTranslation
    {
        $model = $this->getDictionaryModel($dto);

        return $this->updateByModel($model, $dto);
    }

    private function getDictionaryModel(BaseDictionaryDto $dto): BaseHasTranslation
    {
        /**@var BaseHasTranslation $class */
        $class = $this->getModel();

        return $class::find($dto->getModelId());
    }

    public function updateByModel(BaseHasTranslation $model, BaseDictionaryDto $dto): BaseHasTranslation
    {
        if ($dto->getActive() === false) {
            $this->checkOffModel($model);
        }

        return $this->modifyDictionaryModel($model, $dto);
    }

    /**
     * @param BaseHasTranslation $model
     * @throws TranslatedException
     */
    abstract protected function checkOffModel(BaseHasTranslation $model): void;

    public function delete(BaseDictionaryDto $dto): bool
    {
        $model = $this->getDictionaryModel($dto);

        $this->checkOffModel($model);

        $model->delete();

        return true;
    }

    public function toggleActive(BaseDictionaryDto $dto): BaseHasTranslation
    {
        $model = $this->getDictionaryModel($dto);

        return $this->toggleActiveForModel($model);
    }

    public function toggleActiveForModel(BaseHasTranslation $model): BaseHasTranslation
    {
        if ($model->active === true) {
            $this->checkOffModel($model);
        }

        $model->active = !$model->active;

        $model->save();

        return $model;
    }

    abstract public function getList(array $args, HasGuardUser $authUser): ?Collection;
}
