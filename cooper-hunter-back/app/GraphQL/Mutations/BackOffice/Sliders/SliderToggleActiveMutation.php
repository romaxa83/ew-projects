<?php

namespace App\GraphQL\Mutations\BackOffice\Sliders;

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

class SliderToggleActiveMutation extends BaseMutation
{
    public const NAME = 'sliderToggleActiveMutation';
    public const PERMISSION = SliderUpdatePermission::KEY;

    public function __construct(protected SliderService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SliderType::type();
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

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Slider
    {
        return $this->service->toggle(
            Slider::find($args['id'])
        );
    }
}
