<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning;

use App\GraphQL\Types\Commercial\Commissioning\AnswerType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Answer;
use App\Permissions\Commercial\Commissionings\Answer\CreatePermission;
use App\Repositories\Commercial\Commissioning\AnswerRepository;
use App\Services\Commercial\Commissioning\AnswerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class AnswerImageUploadMutation extends BaseMutation
{
    public const NAME = 'commissioningAnswersImageUpload';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected AnswerRepository $repo,
        protected AnswerService $service,
    )
    {
        $this->setTechnicianGuard();
    }

    public function type(): Type
    {
        return AnswerType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Answer::class, 'id')],
                'description' => 'Answer ID'
            ],
            'media' => [
                'type' => FileType::nonNullType(),
                'rules' => ['image'],
            ]
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Answer
    {
        $this->isTechnicianCommercial();

        /** @var $answer Answer */
        $answer = $this->repo->getByFields(['id' => $args['id']]);

        $answer->addMedia($args['media'])
            ->toMediaCollection(Answer::MEDIA_COLLECTION_NAME);

        return $answer;
    }
}

