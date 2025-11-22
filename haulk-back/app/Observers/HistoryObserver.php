<?php

namespace App\Observers;

use App\Models\History\History;
use App\Models\Users\User;
use Auth;
use Str;

class HistoryObserver
{
    /**
     * Listen to the Model created event.
     *
     * @param mixed $model
     * @return void
     */
    public function created($model)
    {
        if (!static::filter('created')) {
            return;
        }

        $model->morphMany(History::class, 'model')->create(
            [
                'message' => trans(
                    'history.created',
                    ['model' => static::getModelName($model)]
                ),
                'user_id' => static::getUserID(),
                'user_role' => static::getUserRole(),
                'performed_at' => time(),
            ]
        );
    }

    /**
     * Listen to the Model updating event.
     *
     * @param mixed $model
     * @return void
     */
    public function updating($model)
    {
        if (!static::filter('updating')) {
            return;
        }

        $changes = $model->getDirty();

        if (array_key_exists('deleted_at', $changes)) {
            return;
        }

        $changed = [];
        foreach ($changes as $key => $value) {
            if (static::isIgnored($model, $key)) {
                continue;
            }

            array_push($changed, ['key' => $key, 'old' => $model->getOriginal($key), 'new' => $model->$key]);
        }
        if (empty($changed)) {
            return;
        }

        $model->morphMany(History::class, 'model')->create(
            [
                'message' => trans(
                    'history.updating',
                    ['model' => static::getModelName($model)]
                ),
                'meta' => $changed,
                'user_id' => static::getUserID(),
                'user_role' => static::getUserRole(),
                'performed_at' => time(),
            ]
        );
    }

    /**
     * Listen to the Model deleting event.
     *
     * @param mixed $model
     * @return void
     */
    public function deleting($model)
    {
        if (!static::filter('deleting')) {
            return;
        }

        $model->morphMany(History::class, 'model')->create(
            [
                'message' => trans(
                    'history.deleting',
                    ['model' => static::getModelName($model)]
                ),
                'user_id' => static::getUserID(),
                'user_role' => static::getUserRole(),
                'performed_at' => time(),
            ]
        );
    }

    /**
     * Listen to the Model restored event.
     *
     * @param mixed $model
     * @return void
     */
    public function restored($model)
    {
        if (!static::filter('restored')) {
            return;
        }

        $model->morphMany(History::class, 'model')->create(
            [
                'message' => trans(
                    'history.restored',
                    ['model' => static::getModelName($model)]
                ),
                'user_id' => static::getUserID(),
                'user_role' => static::getUserRole(),
                'performed_at' => time(),
            ]
        );
    }

    public static function getModelName($model)
    {
        $class = class_basename($model);
        $key = 'history.models.' . Str::snake($class);
        $value = trans($key);

        return $key == $value ? $class : $value;
    }

    public static function getUserID()
    {
        return Auth::check() ? Auth::user()->id : null;
    }

    public static function getUserRole()
    {
        return Auth::check() ? Auth::user()->getRoleNames()->first() : null;
    }

    public static function getUserCompanyTimezone(): ?string
    {
        if (Auth::check() && Auth::user()) {
            $company = Auth::user()->getCompany();

            if ($company) {
                return $company->getTimezone();
            }
        }

        return null;
    }

    public static function isIgnored($model, $key)
    {
        $blacklist = config('history.attributes_blacklist');
        $name = get_class($model);
        $array = $blacklist[$name] ?? null;
        return !empty($array) && in_array($key, $array);
    }

    public static function filter($action)
    {
        if (config('history.enabled') === false) {
            return false;
        }
        if (!Auth::check()) {
            if (in_array('nobody', config('history.user_blacklist'))) {
                return false;
            }
        } elseif (in_array((Auth::user()->id), config('history.user_blacklist'))) {
            return false;
        }

        return is_null($action) || in_array($action, config('history.events_whitelist'));
    }
}
