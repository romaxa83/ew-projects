<?php

namespace App\GraphQL\InputTypes\Catalog\Products;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Catalog\Features\Features\FeaturesForProductInputType;
use App\GraphQL\Types\Catalog\Products\ProductTranslateInputType;
use App\GraphQL\Types\Enums\Catalog\Products\ProductUnitSubTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\UnitType;
use App\Models\Catalog\Troubleshoots;
use App\Models\Catalog\Videos\VideoLink;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class ProductInput extends BaseInputType
{
    public const NAME = 'ProductInput';

    public function fields(): array
    {
        return [
            'guid' => [
                'type' => Type::string(),
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'seer' => [
                'type' => Type::float()
            ],
            'category_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(Category::class, 'id')
                ]
            ],
            'unit_type_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'integer',
                    Rule::exists(UnitType::class, 'id')
                ]
            ],
            'show_rebate' => [
                'type' => Type::boolean(),
            ],
            'relative_category_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => ['nullable', 'array', Rule::exists(Category::class, 'id')]
            ],
            'video_link_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(VideoLink::class, 'id')
                ]
            ],
            'translations' => [
                'type' => ProductTranslateInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
            'features' => [
                'type' => FeaturesForProductInputType::list(),
            ],
            'relations' => [
                'type' => Type::listOf(NonNullType::id()),
                'description' => 'Привязанные товара (аксессуары), пустой массив удалит все связи',
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(Product::class, 'id')
                ]
            ],
            'certificates' => [
                'type' => CertificateInputType::list(),
                'description' => 'Came to replace "certificate_ids" field',
            ],
            'certificate_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'description' => 'Привязанные сертификаты, пустой массив удалит все связи',
                'deprecationReason' => 'Deprecated due to "certificates" field is refactored',
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(Certificate::class, 'id')
                ]
            ],
            'manual_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(Manual::class, 'id')
                ]
            ],
            'label_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(Label::class, 'id')
                ]
            ],
            'specification_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(Specification::class, 'id')
                ],
            ],
            'troubleshoot_group_ids' => [
                'type' => Type::listOf(NonNullType::id()),
                'rules' => [
                    'nullable',
                    'array',
                    Rule::exists(Troubleshoots\Group::class, 'id')
                ]
            ],
            'unit_sub_type' => [
                'type' => ProductUnitSubTypeEnumType::type()
            ],
        ];
    }
}
