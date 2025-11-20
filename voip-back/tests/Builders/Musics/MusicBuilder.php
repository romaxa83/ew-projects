<?php

namespace Tests\Builders\Musics;

use App\Models\Departments\Department;
use App\Models\Musics\Music;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class MusicBuilder extends BaseBuilder
{
    protected bool $hasRecord = false;

    function modelClass(): string
    {
        return Music::class;
    }

    public function active(bool $value = true): self
    {
        $this->data['active'] = $value;
        return $this;
    }

    public function department(Department $model): self
    {
        $this->data['department_id'] = $model->id;
        return $this;
    }

    public function withRecord(): self
    {
        $this->hasRecord = true;
        return $this;
    }

    public function hold(): self
    {
        $this->data['has_unhold_data'] = true;
        $this->data['unhold_data'] = [];
        return $this;
    }

    public function unholdData(array $data): self
    {
        $this->data['has_unhold_data'] = true;
        $this->data['unhold_data'] = $data;
        return $this;
    }

    protected function afterSave($model): void
    {
        parent::afterSave($model);

        if($this->hasRecord){
            $model
                ->addMedia(
                    UploadedFile::fake()->create('music.mp3', 128)
                        ->mimeType('audio/mpeg')
                )
                ->toMediaCollection(Music::MEDIA_COLLECTION_NAME);
        }
    }

    protected function afterClear(): void
    {
        parent::afterClear();

        $this->hasRecord = false;
    }
}

