<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('inventory_category');

        $categoryUniqueRule = Rule::unique(Category::TABLE_NAME, 'name');
        if ($category) {
            $categoryUniqueRule->ignore($category->id);
        }
        return  [
            'name' => ['required', 'string', $categoryUniqueRule],
        ];
    }
}
