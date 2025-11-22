<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Dto\News\NewsDto;
use App\Models\News\News;

class NewsService
{
    public function create(NewsDto $dto): News
    {
        $news = new News();

        return $this->store($news, $dto);
    }

    protected function store(News $news, NewsDto $dto): News
    {
        $this->fill($dto, $news);

        $news->save();

        $this->saveTranslations($news, $dto);

        return $news;
    }

    protected function fill(NewsDto $dto, News $news): void
    {
        $news->tag()->associate($dto->getTagId());
        $news->active = $dto->getActive();
        $news->slug = $dto->getSlug();
        $news->created_at = $dto->getCreatedAt();
    }

    protected function saveTranslations(News $news, NewsDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $news->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                    'short_description' => $translation->getShortDescription(),
                    'seo_title' => $translation->getSeoTitle(),
                    'seo_description' => $translation->getSeoDescription(),
                    'seo_h1' => $translation->getSeoH1(),
                ]
            );
        }
    }

    public function update(News $news, NewsDto $dto): News
    {
        return $this->store($news, $dto);
    }

    public function toggle(News $news): News
    {
        $news->active = !$news->active;
        $news->save();

        return $news;
    }

    public function delete(News $news): bool
    {
        return $news->delete();
    }
}
