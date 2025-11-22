<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning;

use App\GraphQL\Types\Commercial\Commissioning\AnswerType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Answer;
use App\Models\Media\Media;
use App\Permissions\Commercial\Commissionings\Answer\CreatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AnswerImageDeleteMutation extends BaseMutation
{
    public const NAME = 'commissioningAnswersImageDelete';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Media::class, 'id')],
                'description' => 'Media ID'
            ],
        ];
    }

    public function type(): Type
    {
        return AnswerType::type();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Answer
    {
        $this->isTechnicianCommercial();

        $media = Media::query()->where('id', $args['id'])->firstOrFail();

        $answer = $media->model;

        $media->delete();

        return $answer;
    }
}

