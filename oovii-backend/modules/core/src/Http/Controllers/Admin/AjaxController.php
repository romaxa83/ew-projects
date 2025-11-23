<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Gate;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;

class AjaxController extends AdminController
{
    use AjaxResponseStatusTrait;

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function generateSlug(Request $request)
    {
        $separator = $request->get('separator') ?: '-';
        $language = $request->get('language') ?: \App::getLocale();

        $slug = str_slug($request->get('slug'), $separator, $language);

        return $this->success(compact('slug'));
    }

    /**
     * @param $model
     * @param  string  $action
     * @return bool
     */
    private function authorizeForModel($model, $action = 'edit')
    {
        $obj = is_object($model) ? $model : new $model();

        return Gate::allows(app(PermissionsContainerInterface::class)->getModelAbility($obj, $action), $model);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function changeStatus(Request $request): JsonResponse
    {
        $model = decrypt($request->get('model'));
        $id = $request->get('id');
        $status = !$request->get('status');
        $field = $request->get('field', 'published');
        $locale = $request->get('locale');

        if (!$model || !$id) {
            $this->error(__('cms-core::admin.layout.Insufficient data to perform the operation'));
        }

        /** @var Model|Translatable $obj */
        $obj = $model::find($id);
        if (!$obj) {
            return $this->error(__('cms-core::admin.layout.Object not found'));
        }

        if(($obj instanceof Collection) && $status) {
            if($msg = $obj->checkForPublished()){
                return $this->error($msg);
            }
        }

        if (!$this->authorizeForModel($obj)) {
            return $this->error(__('cms-core::admin.auth.Access is denied!'));
        }

        $isTranslatable = method_exists($obj, 'translate');
        if ($isTranslatable) {
            $obj->loadMissing('translations');
            // Unset translation relation for correct localized fields update
            $obj->unsetRelation('translation');
        }

        if ($isTranslatable && $obj->isTranslationAttribute($field)) {
            if (!$locale) {
                return $this->error(__('cms-core::admin.layout.No language specified'));
            }

            $obj->translateOrNew($locale)->{$field} = $status;

            $field = "$locale.$field";
        } else {
            $obj->{$field} = $status;
        }

        try {
            if ($modelRequest = $request->get('model_request')) {
                $this->validateChangeStatus($obj, $modelRequest, $isTranslatable, $field, $status);
            }
        } catch (ValidationException $e) {
            return $this->error(collect($e->validator->errors()->messages())->flatten()->implode('<br>'));
        }

        if ($obj->update()) {
            return $this->success([
                'status' => $status,
                'button_text' => $status ? $request->get('text_on') : $request->get('text_off')
            ]);
        }

        return $this->error(__('cms-core::admin.layout.Error updating data'));
    }

    /**
     * @param  Model|Translatable  $obj
     * @param  string  $modelRequest
     * @param  bool  $isTranslatable
     * @param  string  $field
     * @param  bool  $status
     * @return void
     * @throws ValidationException
     */
    protected function validateChangeStatus(
        Model $obj,
        string $modelRequest,
        bool $isTranslatable,
        string $field,
        bool $status
    ) {
        try {
            $modelRequest = decrypt($modelRequest);
            /** @var FormRequest|null $modelRequest */
            $modelRequest = new $modelRequest();
        } catch (DecryptException $e) {
            return;
        }

        $data = $obj->toArray();
        if ($isTranslatable) {
            foreach ($data['translations'] as $translation) {
                $data[$translation['locale']] = $translation;
            }
            unset($data['translations']);
        }

        // Convert to int for required_if rule that uses strict comparison
        data_set($data, $field, (int)$status);

        // Remove images & files fields
        $fieldsToRemove = [];
        if (method_exists($obj, 'imageSettings')) {
            $fieldsToRemove += array_keys($obj->imageSettings());
        }
        if (method_exists($obj, 'fileSettings')) {
            $fieldsToRemove += array_keys($obj->fileSettings());
        }

        $locales = array_keys(app('locales'));
        foreach ($fieldsToRemove as $item) {
            if ($isTranslatable && $obj->isTranslationAttribute($item)) {
                foreach ($locales as $locale) {
                    if (array_key_exists($item, $data[$locale])) {
                        unset($data[$locale][$item]);
                    }
                }
            } elseif (array_key_exists($item, $data)) {
                unset($data[$item]);
            }
        }

        // Setup route resolver with given row key.
        $modelRequest->setRouteResolver(function () use ($obj) {
            $route = app(Route::class);
            $route->setParameter(snake_case(str_singular(class_basename($obj))), $obj->getKey());

            return $route;
        });

        \Validator::make(
            $data,
            $modelRequest->rules(),
            $modelRequest->messages(),
            $modelRequest->attributes()
        )->validate();
    }

    /**
     * @param $modelClass
     * @return bool
     */
    private function sortingAccess($modelClass)
    {
        $abilityPrefix = app(PermissionsContainerInterface::class)->getModelAbility(new $modelClass());

        return Gate::has("{$abilityPrefix}.sort")
            ? Gate::allows("{$abilityPrefix}.sort", $modelClass)
            : Gate::allows("{$abilityPrefix}.view");
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateSort(Request $request)
    {
        $column = $request->get('column', 'sort');
        $model = decrypt($request->get('model'));
        $positions = $request->get('positions');
        $offset = ($request->get('page', 1) - 1) * $request->get('limit', 0);

        if (!$this->sortingAccess($model)) {
            return $this->error(__('cms-core::admin.auth.Access is denied!'));
        }

        foreach ($positions as $position => $id) {
            $obj = $model::findOrFail($id);
            $obj->$column = $position + $offset;
            $obj->save();
        }

        return $this->success(__('cms-core::admin.layout.Sort updated'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateNestableSort(Request $request)
    {
        $model = decrypt($request->get('model'));
        $updateFields = $request->get('update_fields', []);
        $sortField = $request->get('sort_field', 'sort');
        $parentField = $request->get('parent_field', 'parent_id');
        $items = $request->get('items', []);

        if (!$this->sortingAccess($model)) {
            return $this->error(__('cms-core::admin.auth.Access is denied!'));
        }

        $this->saveSort($items, null, $model, $updateFields, $sortField, $parentField);

        return $this->success();
    }

    public function markNotificationsAsRead()
    {
        \Auth::guard('admin')->user()->unreadNotifications->markAsRead();

        return $this->success(__('cms-core::admin.notifications.All notifications are marked as read'));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function markNotificationAsRead($id)
    {
        \Auth::guard('admin')->user()->notifications()->where('id', $id)->get()->markAsRead();

        return $this->success();
    }

    /**
     * @param $arr
     * @param $parentId
     * @param $model
     * @param $updateFields
     * @param $sortField
     * @param $parentField
     */
    protected function saveSort($arr, $parentId, $model, $updateFields, $sortField, $parentField)
    {
        foreach ($arr as $sort => $item) {
            $id = array_get($item, 'id');
            $model::find($id)
                ->update(array_merge($updateFields, [$sortField => $sort, $parentField => $parentId]));

            $children = array_get($item, 'children', []);
            if ($children) {
                $this->saveSort($children, $id, $model, $updateFields, $sortField, $parentField);
            }
        }
    }
}
