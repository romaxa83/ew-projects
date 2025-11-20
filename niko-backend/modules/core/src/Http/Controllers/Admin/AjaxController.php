<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Gate;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;

class AjaxController extends AdminController
{
    use AjaxResponseStatusTrait;

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
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

        $gate = (property_exists($obj, 'abilityPrefix')
                ? $obj->abilityPrefix
                : str_replace('_', '-', $obj->getTable())) . '.' . $action;

        return Gate::allows($gate, $model);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $model = decrypt($request->get('model'));
        $id = $request->get('id');
        $status = !$request->get('status');
        $field = $request->get('field', 'published');
        $locale = $request->get('locale');
        try {
            $modelRequest = $request->get('model_request');
            if ($modelRequest) {
                $modelRequest = decrypt($modelRequest);
                /** @var FormRequest|null $modelRequest */
                $modelRequest = new $modelRequest();
            }
        } catch (DecryptException $e) {
            $modelRequest = null;
        }

        if (!$model || !$id) {
            $this->error(__('cms-core::admin.layout.Insufficient data to perform the operation'));
        }

        /** @var Model $obj */
        $obj = $model::find($id);
        if (!$obj) {
            return $this->error(__('cms-core::admin.layout.Object not found'));
        }

        if (!$this->authorizeForModel($obj)) {
            return $this->error(__('cms-core::admin.auth.Access is denied!'));
        }

        $isTranslatable = method_exists($obj, 'translate');
        if (!array_key_exists($field, $obj->getAttributes()) && $isTranslatable) {
            if (!$locale) {
                return $this->error(__('cms-core::admin.layout.No language specified'));
            }

            $obj->translateOrNew($locale)->{$field} = $status;
        } else {
            $obj->{$field} = $status;
        }

        if ($modelRequest) {
            $data = $obj->toArray();
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $data[$translation['locale']] = $translation;
                }
                unset($data['translations']);
            }

            // Remove images & files fields
            $fieldsToRemove = [];
            if (method_exists($obj, 'imageSettings')) {
                $fieldsToRemove += array_keys($obj->imageSettings());
            }
            if (method_exists($obj, 'fileSettings')) {
                $fieldsToRemove += array_keys($obj->fileSettings());
            }

            $locales = array_keys(app('locales'));
            foreach ($fieldsToRemove as $field) {
                if ($isTranslatable && $obj->isTranslationAttribute($field)) {
                    foreach ($locales as $locale) {
                        if (array_key_exists($field, $data[$locale])) {
                            unset($data[$locale][$field]);
                        }
                    }
                } else {
                    if (array_key_exists($field, $data)) {
                        unset($data[$field]);
                    }
                }
            }

            // Setup route resolver with given row key.
            $modelRequest->setRouteResolver(function () use ($obj) {
                $route = app(Route::class);
                $route->setParameter(snake_case(str_singular(class_basename($obj))), $obj->getKey());

                return $route;
            });

            $validator = \Validator::make(
                $data,
                $modelRequest->rules(),
                $modelRequest->messages(),
                $modelRequest->attributes()
            );
            if ($validator->fails()) {
                return $this->error(collect($validator->errors()->messages())->flatten()->implode('<br>'));
            }
        }

        if ($obj->update()) {
            return $this->success([
                'status' => $status,
                'button_text' => $status ? $request->get('text_on') : $request->get('text_off')
            ]);
        } else {
            return $this->error(__('cms-core::admin.layout.Error updating data'));
        }
    }

    /**
     * @param $modelClass
     * @return bool
     */
    private function sortingAccess($modelClass)
    {
        $obj = new $modelClass();

        $abilityPrefix = property_exists($obj, 'abilityPrefix')
            ? $obj->abilityPrefix
            : str_replace('_', '-', $obj->getTable());

        $sortGate = $abilityPrefix . '.sort';

        if (Gate::has($sortGate)) {
            return Gate::allows($sortGate, $modelClass);
        } else {
            return Gate::allows($abilityPrefix . '.view');
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
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

        function saveSort($arr, $parentId, $model, $updateFields, $sortField, $parentField)
        {
            foreach ($arr as $sort => $item) {
                $id = array_get($item, 'id');
                $model::find($id)
                    ->update(array_merge($updateFields, [$sortField => $sort, $parentField => $parentId]));

                $children = array_get($item, 'children', []);
                if ($children) {
                    saveSort($children, $id, $model, $updateFields, $sortField, $parentField);
                }
            }
        }

        saveSort($items, null, $model, $updateFields, $sortField, $parentField);

        return $this->success();
    }

    public function markNotificationsAsRead()
    {
        \Auth::guard('admin')->user()->unreadNotifications->markAsRead();

        return $this->success(__('cms-core::admin.notifications.All notifications are marked as read'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationAsRead($id)
    {
        \Auth::guard('admin')->user()->notifications()->where('id', $id)->get()->markAsRead();

        return $this->success();
    }
}
