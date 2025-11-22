<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Categories;

use App\Dto\Catalog\CategoryDto;
use App\GraphQL\Types\Catalog\Categories\CategoryTranslateInputType;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\GraphQL\Types\Enums\Catalog\CategoryTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Categories\UpdatePermission;
use App\Rules\Catalog\Categories\MainCategoryRule;
use App\Rules\TranslationsArrayValidator;
use App\Services\Catalog\Categories\CategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CategoryUpdateMutation extends BaseMutation
{
    public const NAME = 'categoryUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(protected CategoryService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CategoryType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'guid' => [
                'type' => Type::string(),
            ],
            'sort' => [
                'type' => Type::int()
            ],
            'active' => [
                'type' => Type::boolean()
            ],
            'main' => [
                'type' => Type::boolean(),
                'description' => 'Display as main category on homepage (Can be many)',
            ],
            'type' => [
                'type' => CategoryTypeEnumType::type(),
            ],
            'parent_id' => [
                'type' => Type::id(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
                'rules' => [Rule::unique(Category::class, 'slug')],
            ],
            'enable_seer' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => false,
            ],
            'translations' => [
                'type' => CategoryTranslateInputType::nonNullList(),
            ]
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Category
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Category
    {
        return makeTransaction(
            fn() => $this->service->update(
                CategoryDto::byArgs($args),
                Category::find($args['id'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'guid' => ['nullable', 'uuid', Rule::unique(Category::class, 'guid')->ignore($args['id'])],
            'sort' => ['nullable', 'integer'],
            'active' => ['nullable', 'boolean'],
            'main' => ['nullable', 'boolean', new MainCategoryRule($args['id'])],
            'parent_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'slug' => ['required', 'string', Rule::unique(Category::class, 'slug')->ignore($args['id'])],
            'translations' => [new TranslationsArrayValidator()],
            'translations.*.language' => ['required', 'max:3', Rule::exists(Language::class, 'slug')],
            'translations.*.title' => ['required', 'max:250'],
            'translations.*.description' => ['nullable'],
        ];
    }
}
