<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Dto\News\VideoDto;
use App\Models\News\Video;

class VideoService
{
    public function create(VideoDto $dto): Video
    {
        $video = new Video();

        return $this->store($video, $dto);
    }

    protected function store(Video $video, VideoDto $dto): Video
    {
        $this->fill($dto, $video);

        $video->save();

        $this->saveTranslations($video, $dto);

        return $video;
    }

    protected function fill(VideoDto $dto, Video $video): void
    {
        $video->active = $dto->getActive();
        $video->slug = $dto->getSlug();
        $video->created_at = $dto->getCreatedAt();
    }

    protected function saveTranslations(Video $video, VideoDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $video->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'video_link' => $translation->getVideoLink(),
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                    'seo_title' => $translation->getSeoTitle(),
                    'seo_description' => $translation->getSeoDescription(),
                    'seo_h1' => $translation->getSeoH1(),
                ]
            );
        }
    }

    public function update(Video $video, VideoDto $dto): Video
    {
        return $this->store($video, $dto);
    }

    public function toggle(Video $video): Video
    {
        $video->active = !$video->active;
        $video->save();

        return $video;
    }

    public function delete(Video $video): bool
    {
        return $video->delete();
    }
}
