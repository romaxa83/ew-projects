<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Features;

use App\Dto\Catalog\FeatureDto;
use App\GraphQL\Types\Catalog\Features\Features\FeatureType;
use App\GraphQL\Types\Catalog\Features\Features\TranslateInputType;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Features\CreatePermission;
use App\Rules\Catalog\Features\DisplayInWebRule;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\FeatureService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FeatureCreateMutation extends BaseMutation
{
    public const NAME = 'featureCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected FeatureService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return FeatureType::type();
    }

    public function args(): array
    {
        return [
            'guid' => Type::string(),
            'sort' => Type::int(),
            'active' => Type::boolean(),
            'display_in_mobile' => Type::boolean(),
            'display_in_web' => Type::boolean(),
            'display_in_filter' => Type::boolean(),
            'translations' => TranslateInputType::nonNullList(),
        ];
    }

    /** @throws Throwable */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Feature
    {
        return makeTransaction(
            fn() => $this->service->create(
                FeatureDto::byArgs($args)
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'sort' => ['nullable', 'integer'],
            'guid' => ['nullable', 'uuid', Rule::unique(Feature::class, 'guid')],
            'active' => ['nullable', 'boolean'],
            'display_in_mobile' => ['nullable', 'boolean'],
            'display_in_web' => ['nullable', 'boolean', new DisplayInWebRule()],
            'translations' => [new TranslationsArrayValidator()],
            'translations.*.language' => ['required', 'max:3', 'exists:languages,slug'],
            'translations.*.title' => ['required', 'max:250'],
            'translations.*.description' => ['nullable'],
        ];
    }
}
