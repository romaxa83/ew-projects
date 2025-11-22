<?php

namespace App\Foundations\Modules\History\Contracts;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Users\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

interface HistoryServiceInterface
{
    public function setUser(User $user): self;
    public function setModel(BaseModel $model): self;
    public function setAction(string $action): self;
    public function setMedia(Media|SpatieMedia|null $model): self;
    public function setComment(Comment|null $model): self;
    public function setAdditional(array $data): self;
    public function exec(): void;
}
