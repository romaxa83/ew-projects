<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Sliders;

use App\Dto\Sliders\SliderDto;
use App\GraphQL\InputTypes\Sliders\SliderInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Sliders\SliderType;
use App\Models\Sliders\Slider;
use App\Permissions\Sliders\SliderUpdatePermission;
use App\Services\Sliders\SliderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SliderUpdateMutation extends BaseMutation
{
    public const NAME = 'sliderUpdate';
    public const PERMISSION = SliderUpdatePermission::KEY;

    public function __construct(private SliderService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SliderType::nonNullType();
    }

    public function args(): array
    {
        return [
            'slider_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Slider::TABLE, 'id')],
            ],
            'slider' => [
                'type' => SliderInput::nonNullType()
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
    ): Slider {
        return makeTransaction(
            fn() => $this->service->update(
                Slider::find($args['slider_id']),
                SliderDto::byArgs($args['slider'])
            )
        );
    }
}
