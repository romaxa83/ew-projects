<?php

namespace App\Traits;

use Arr;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Request;
use DB;

/**
 * Trait ModelMain
 *
 * Методы для работы с основной таблицей, имеющей таблицу в БД с переводами
 *
 * @package App\Traits
 * @property Collection $data
 * @property $this $current
 * @property object|mixed|null $dataFor
 */
trait ModelMain
{

    public function relatedModelName()
    {
        $currentClass = static::class;
        $translatesClass = $currentClass . 'Translates';
        return $translatesClass;
    }

    public function getRelatedTableName()
    {
        $relatedModelName = $this->relatedModelName();
        $relatedModel = new $relatedModelName;
        return $relatedModel->getTable();
    }

    protected function _rules()
    {
        return [];
    }

    public function rules()
    {
        $rules = $this->_rules();
        $modelName = $this->relatedModelName();
        $model = new $modelName;
        $translatesRules = method_exists($model, 'rules') ? $model->rules() : [];
        foreach ($translatesRules AS $key => $rule) {
            foreach (config('languages', []) AS $language) {
                $rules[$language['slug'] . '.' . $key] = $rule;
            }
        }
        return $rules;
    }

    public function data()
    {
        return $this->hasMany($this->relatedModelName(), 'row_id', 'id');
    }

    public function current()
    {
        return $this
            ->hasOne($this->relatedModelName(), 'row_id', 'id')
            ->where('language', '=', app()->getLocale());
    }

    public function dataFor($lang, $default = null)
    {
        $data = $this->data;
        foreach ($data as $element) {
            if ($element->language == $lang) {
                return $element;
            }
        }
        return $default;
    }

    /**
     * @param Request|\Illuminate\Http\Request|array $request
     * @return bool
     * @throws Exception
     */
    public function createRow($request)
    {
        try {
            DB::beginTransaction();
            if (is_array($request)) {
                $this->fill($request);
            } else {
                $this->fill($request->input() ?: []);
            }
            if ($this->save() !== true) {
                return false;
            }
            $modelName = $this->relatedModelName();
            foreach (config('languages') AS $language) {
                $translate = new $modelName();
                $translate->fill((is_array($request) ? Arr::get($request, $language['slug'], []) : $request->input($language['slug']))
                        ?: []
                );
                $translate->language = $language['slug'];
                $translate->row_id = $this->id;
                $translate->save();
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
        return true;
    }

    /**
     * @param Request|\Illuminate\Http\Request $request
     * @return bool
     * @throws Exception
     */
    public function updateRow($request)
    {
        try {
            DB::beginTransaction();
            $this->fill($request->input());
            $changes = [];
            if ($this->isDirty()) {
                $changes['main'] = array_keys($this->getDirty());
            }
            if ($this->save() !== true) {
                return false;
            }
            $modelName = $this->relatedModelName();
            foreach (config('languages') AS $language) {
                $translate = $this->dataFor($language['slug']);
                if (!$translate) {
                    $translate = new $modelName();
                }
                $translate->fill($request->input($language['slug'], []));
                $translate->row_id = $this->id;
                $translate->language = $language['slug'];
                if ($translate->isDirty()) {
                    $changes[$translate->language] = array_keys($translate->getDirty());
                }
                $translate->save();
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            return false;
        }
        return true;
    }

    public function deleteRow()
    {
        try {
            if (method_exists($this, 'deleteImage')) {
                $this->deleteImage();
            }
            $this->delete();
        } catch (Exception $exception) {
            return false;
        }
        return true;
    }

}
