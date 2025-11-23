<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;

class CollectionFilter extends ModelFilter implements FilterListFieldsInterface
{
    public $relations = [
        'products' => [
            'with_products' => 'availableProduct',
        ],
    ];

    public function getFields(): iterable
    {
        $adminRepo = app(AdminRepository::class);
        $moderators = $adminRepo->getByRoleForSelect(Role::DEFAULT_MODERATOR);
//        $optionsTree = view(
//            'cms-catalog::admin.categories.categories-options',
//            ['tree' => Category::getForSelect(), 'name' => 'category_id']
//        );

        return [
            FilterField::makeName(),
            FilterField::published(),
            FilterField::make()
                ->type(FilterField::TYPE_SELECT)
                ->options($moderators)
                ->name('moderator_id')
                ->label(__('cms-catalog::admin.moderator'))
                ->class('js-select2'),
//            FilterField::make()
//                ->type(FilterField::TYPE_SELECT_WITH_CUSTOM_OPTIONS)
//                ->customOptions($optionsTree)
//                ->name('category_id')
//                ->label(__('cms-catalog::admin.products.Category'))
//                ->class('js-select2'),
            FilterField::make()
                ->name('start_at')
                ->label(__('cms-catalog::admin.collection.start_at'))
                ->size(3)
                ->type(FilterField::TYPE_DATE_RANGE),
            FilterField::make()
                ->name('end_at')
                ->label(__('cms-catalog::admin.collection.end_at'))
                ->size(3)
                ->type(FilterField::TYPE_DATE_RANGE),
            FilterField::make()
                ->name('created_at')
                ->label(__('cms-core::admin.layout.Created at'))
                ->type(FilterField::TYPE_DATE_RANGE),
        ];
    }

    public function name($name): void
    {
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function published($published): void
    {
        $this->where('published', $published);
    }

    public function createdAtFrom($date): void
    {
        $this->where('created_at', '>=', Carbon::parse($date));
    }

    public function createdAtTo($date): void
    {
        $this->where('created_at', '<=', Carbon::parse($date)->endOfDay());
    }

    public function startAtFrom($date): void
    {
        $this->where('start_at', '>=', Carbon::parse($date));
    }

    public function startAtTo($date): void
    {
        $this->where('start_at', '<=', Carbon::parse($date)->endOfDay());
    }

    public function endAtFrom($date): void
    {
        $this->where('end_at', '>=', Carbon::parse($date));
    }

    public function endAtTo($date): void
    {
        $this->where('end_at', '<=', Carbon::parse($date)->endOfDay());
    }

    public function moderator($id): void
    {
        $this->where('moderator_id', $id);
    }

//    public function category($id)
//    {
//        $this->where('category_id', $id);
//    }
}

