<?php

namespace App\Foundations\Modules\History\Services;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Contracts\HistoryServiceInterface;
use App\Foundations\Modules\History\Dto\HistoryDto;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Strategies\Details\DetailsStrategy;
use App\Foundations\Modules\History\Strategies\Details\DummyStrategy;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Settings\Settings;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

abstract class HistoryService implements HistoryServiceInterface
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';

    public const ACTION_COMMENT_CREATED = 'comment_created';
    public const ACTION_COMMENT_DELETED = 'comment_deleted';

    public const ACTION_DELETE_FILE = 'delete_file';
    public const ACTION_UPLOAD_FILE = 'upload_file';

    public const HISTORY_MESSAGE_COMMENT_CREATED = 'history.comment.created';
    public const HISTORY_MESSAGE_COMMENT_DELETED = 'history.comment.deleted';

    public const HISTORY_MESSAGE_DELETED_FILE = 'history.media.deleted';
    public const HISTORY_MESSAGE_UPLOAD_FILE = 'history.media.upload';

    protected BaseModel $model;
    protected User|null $user;

    protected Comment|null $comment;

    protected Media|SpatieMedia|null $media;
    protected string|null $action;
    protected array $additional = [];

    public string $type = HistoryType::CHANGES;

    protected abstract function getMsg(): string;

    protected abstract function getDetailsStrategy(): DetailsStrategy;

    protected abstract function getMsgAttr(): array;

    protected function getHistoryType(): string
    {
        return HistoryType::CHANGES;
    }

    public function setModel(BaseModel $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setAdditional(array $data): self
    {
        $this->additional = $data;
        return $this;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function setMedia(Media|SpatieMedia|null $model): self
    {
        $this->media = $model;
        return $this;
    }

    public function setComment(Comment|null $model): self
    {
        $this->comment = $model;
        return $this;
    }

    public function create(HistoryDto $dto): History
    {
        $model = new History();

        $model->type = $dto->type;
        $model->msg = $dto->msg;
        $model->msg_attr = $dto->msgAttr;
        $model->model_id = $dto->modelId;
        $model->model_type = $dto->modelType;
        $model->user_id = $dto->userId;
        $model->user_role = $dto->userRole;
        $model->performed_at = $dto->performedAt;
        $model->performed_timezone = $dto->performedTimezone;
        $model->details = $dto->details;

        $model->save();

        return $model;
    }

    public function exec(): void
    {
        try {
            $data = [
                'type' => $this->getHistoryType(),
                'msg' => $this->getMsg(),
                'msg_attr' => $this->getMsgAttr(),
                'model_type' => defined($this->model::class . '::MORPH_NAME')
                    ? $this->model::MORPH_NAME
                    : $this->model::class,
                'model_id' => $this->model->id,
                'user_id' => $this->user?->id,
                'user_role' => $this->user?->role_name_pretty,
                'performed_at' => CarbonImmutable::now(),
                'performed_timezone' => Settings::getParam('timezone'),
                'details' => $this->getDetailsStrategy()->getDetails(),
                'details_empty' => $this->getDetailsStrategy() instanceof DummyStrategy,
            ];

            if(!empty($data['details'])){
                $this->create(HistoryDto::byArgs($data));
            } elseif ($data['details_empty']){
                $this->create(HistoryDto::byArgs($data));
            }

        } catch (\Throwable $e){
            logger_info('HISTORY FAIL', [$e]);
        }
    }
}
