<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Validator;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue as ValuesModel;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class SpecValuesController extends AdminController implements SpecValuesInterface
{
    use AjaxResponseStatusTrait;
    use LocalizedRequestTrait;

    /**
     * @return string|null
     */
    protected function abilityPrefix(): ?string
    {
        return 'specifications';
    }

    /**
     * Get all elements list and locales.
     *
     * @param $specificationId
     * @param  Request  $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function list($specificationId, Request $request): JsonResponse
    {
        /** @var Specification $specification */
        $specification = Specification::findOrFail($specificationId);

        $this->authorizeForAction('view', $specification);

        /** @var ValuesModel[]|Collection $dbSpecifications */
        $dbSpecifications = $specification->specValues()->sorting()->get();

        $items = [];
        $locales = app('locales');

        $rowNumber = 1;
        foreach ($dbSpecifications as $specValue) {
            $items[$specValue->id] = [
                'id' => $specValue->id,
                'published' => $specValue->published,
                'slug' => $specValue->slug,
                'color' => $specValue->color,
                'sort' => $specValue->sort,
                'number' => $rowNumber++,
            ];

            foreach ($locales as $locale => $language) {
                $langRow = $specValue->translateOrNew($locale);
                $items[$specValue->id][$locale] = [
                    'name' => $langRow->name,
                ];
            }
        }

        $items = array_values($items);

        return $this->success(compact('items'));
    }

    /**
     * Create new specification value.
     *
     * @param $specificationId
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws AuthorizationException
     */
    public function create($specificationId, Request $request): JsonResponse
    {
        /** @var Specification $specification */
        $specification = Specification::findOrFail($specificationId);

        $this->authorizeForAction('edit', $specification);

        $data = [
            'published' => $request->get('published'),
            'slug' => $request->get('slug'),
            'color' => $request->get('color'),
        ];

        foreach ($request->get('name') as $locale => $name) {
            $data[$locale] = ['name' => $name];
        }

        Validator::make($data, $this->getRules())
            ->setAttributeNames($this->getRuleAttributes())
            ->validate();

        $specification->specValues()->create($data);

        return $this->success();
    }

    /**
     * Save all specification.
     *
     * @param $specificationId
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     * @throws AuthorizationException
     */
    public function save($specificationId, Request $request): JsonResponse
    {
        /** @var Specification $specification */
        $specification = Specification::findOrFail($specificationId);

        $this->authorizeForAction('edit', $specification);

        $locales = array_keys((array) app('locales'));

        $correctData = [];
        foreach ($request->get('data', []) as $item) {
            $id = $item['id'];

            $correctData[$id] = [
                'sort' => $item['sort'],
                'published' => $item['published'],
                'slug' => str_slug($item['slug']),
                'color' => array_get($item, 'color'),
            ];

            foreach ($locales as $locale) {
                $correctData[$id][$locale] = [
                    'name' => array_get($item, $locale . '.name'),
                ];
            }
        }

        $this->validateAllRows($correctData);

        DB::transaction(function () use ($correctData, $specification) {
            // Update
            foreach ($correctData as $id => $data) {
                $row = ValuesModel::findOrFail($id);
                $row->fill($data);
                $row->save();
            }

            // Deleted items
            if (count($correctData)) {
                ValuesModel::where('specification_id', $specification->id)
                    ->whereNotIn('id', array_keys($correctData))->delete();
            } else {
                ValuesModel::where('specification_id', $specification->id)->delete();
            }
        });

        return $this->success();
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        $specValue = ValuesModel::find($id);

        if (!$specValue) {
            return $this->success();
        }

        $specification = $specValue->specification;

        $this->authorizeForAction('edit', $specification);

        return ValuesModel::destroy($id) ? $this->success() : $this->error();
    }

    /**
     * @param  int|null  $id
     * @return array
     */
    private function getRules(int $id = null): iterable
    {
        return $this->localizeRules(
            ['name' => 'required'],
            ['slug' => 'required|alpha_dash|unique:spec_values,slug,' . $id]
        );
    }

    /**
     * @return array
     */
    private function getRuleAttributes(): array
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-catalog::admin.specifications.Name'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
                'slug' => __('cms-core::admin.layout.Slug'),
            ]
        );
    }

    /**
     * @param  array  $correctData
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateAllRows(array $correctData)
    {
        $rowNumber = 1;
        foreach ($correctData as $id => $data) {
            $validator = Validator::make($data, $this->getRules($id));

            $attributes = [
                'slug' => sprintf(
                    '%s %s: %d',
                    __('cms-core::admin.layout.Slug'),
                    __('cms-catalog::admin.specifications.row'),
                    $rowNumber
                ),
            ];

            foreach (app('locales') as $locale => $language) {
                $attributes[$locale . '.name'] = sprintf(
                    '%s (%s) %s: %d',
                    __('cms-catalog::admin.specifications.Name'),
                    $language,
                    __('cms-catalog::admin.specifications.row'),
                    $rowNumber
                );
            }

            $validator->setAttributeNames($attributes);
            $validator->validate();
            $rowNumber++;
        }
    }
}
