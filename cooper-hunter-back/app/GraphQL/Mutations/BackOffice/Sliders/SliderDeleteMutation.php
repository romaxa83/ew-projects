<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Sliders;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Sliders\Slider;
use App\Permissions\Sliders\SliderDeletePermission;
use App\Services\Sliders\SliderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SliderDeleteMutation extends BaseMutation
{
    public const NAME = 'sliderDelete';
    public const PERMISSION = SliderDeletePermission::KEY;

    public function __construct(private SliderService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Slider::TABLE, 'id')],
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        makeTransaction(fn() => $this->service->delete(Slider::find($args['id'])));

        return ResponseMessageEntity::success(__('Entity deleted'));
    }
}
