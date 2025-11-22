<?php

namespace App\Services\Catalog\Video;

use App\Dto\Catalog\Video\LinkDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Catalog\Videos\VideoLinkTranslation;
use App\Traits\Model\ToggleActive;
use Exception;
use Illuminate\Support\Str;
use Throwable;

class LinkService
{
    use ToggleActive;

    public function create(LinkDto $dto): VideoLink
    {
        $model = new VideoLink();

        $this->fill($dto, $model);
        $model->save();

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDTO */
            $t = new VideoLinkTranslation();
            $t->row_id = $model->id;
            $t->language = $translation->getLanguage();
            $t->title = $translation->getTitle();
            $t->slug = Str::slug($translation->getTitle());
            $t->description = $translation->getDescription();
            $t->save();
        }

        return $model;
    }

    private function fill(LinkDto $dto, VideoLink $model): void
    {
        $model->link_type = $dto->getLinkType();
        $model->active = $dto->getActive();
        $model->link = $dto->getLink();
        $model->group_id = $dto->getGroupId();
    }

    public function update(LinkDto $dto, VideoLink $model): VideoLink
    {
        $this->fill($dto, $model);
        $model->save();

        foreach ($dto->getTranslations() ?? [] as $translation) {
            /** @var $translation SimpleTranslationDTO */
            /** @var $t VideoLinkTranslation */
            $t = $model->translations->where('language', $translation->getLanguage())->first();
            $t->title = $translation->getTitle();
            $t->slug = Str::slug($translation->getTitle());
            $t->description = $translation->getDescription();
            $t->save();
        }

        $model->refresh();

        return $model;
    }

    public function remove(VideoLink $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}


