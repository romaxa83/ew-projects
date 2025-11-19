<?php

declare(strict_types=1);

namespace Wezom\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Wezom\Core\Dto\TranslationDto;
use Wezom\Core\Models\Translation;

class TranslationService
{
    public function create(TranslationDto $dto): Translation
    {
        $translate = new Translation();

        $this->fill($translate, $dto);

        return $translate;
    }

    public function update(int|string $id, TranslationDto $dto): Translation
    {
        $translate = Translation::findOrFail($id);

        $this->fill($translate, $dto);

        return $translate;
    }

    /**
     * @param  Collection<TranslationDto>  $items
     */
    public function insertOrIgnore(Collection $items): array
    {
        $result = [];

        foreach ($items as $dto) {
            /** @var TranslationDto $dto */
            $result[] = Translation::firstOrCreate(
                $dto->only('key', 'language', 'side')->all(),
                $dto->only('text')->all()
            );
        }

        $this->cacheClear();

        return $result;
    }

    protected function fill(Translation $translate, TranslationDto $dto): void
    {
        $translate->fill($dto->all());

        $translate->save();

        $this->cacheClear();
    }

    private function cacheClear(): void
    {
        Cache::tags('translations')->clear();
    }
}
