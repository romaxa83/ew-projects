<?php

namespace App\GraphQL\InputTypes\Stores\Stores;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\StoreCategory;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

class StoreInputType extends BaseInputType
{
    public const NAME = 'StoreInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'link' => [
                'type' => NonNullType::string(),
                'rules' => ['url'],
            ],
            'store_category_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(StoreCategory::class, 'id')]
            ],
            'translations' => [
                'type' => StoreTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
