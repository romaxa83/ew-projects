<?php

namespace App\Http\View\Composers;

use App\Http\Controllers\Mailbox\Gmail\GMailController;
use App\Http\Controllers\Zadarma\PBXController;
use App\Models\Division;
use Illuminate\View\View;
use App\User;
use Cache, Auth;

class MenuComposer
{

    protected $buffer;
    protected $cacheH = 6;
    protected $zadarmaStatus = false;

    /**
     * Create a new profile composer.
     *
     * @param UserRepository $users
     * @return void
     */
    public function __construct()
    {
        // Прогружаем имена всех манагеров т.к. много где юзается + реплейсим на JS
        // FIXME возможно выкинуть?
        $this->buffer['nav_managers'] = Cache::remember('nav_managers', now()->addHours($this->cacheH), function () {
            return User::all(['id', 'name'])->keyBy('id');
        });

        $this->buffer['divisions'] = Cache::remember('nav_divisions', now()->addDays(10), function () {
            return Division::all(['id', 'name', 'title', 'miscs'])->keyBy('id');
        });
    }


    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
//        $view->with('nav', $this->buffer['nav']);
        $view->with('nav_managers', $this->buffer['nav_managers']);

//        dd($this->buffer['divisions']);
        $data = array_merge([
            'records' => $this->buffer['divisions'],
        ],
            request()->session()->get('division')
        );

        $is_zadarma_enabled = Cache::remember('is_zadarma_enabled_d' . session()->get('division.id') . '_' . Auth::id(), now()->addHours($this->cacheH), function () {
            return (new PBXController())->isAllowedCallWidget();
        });
        $zadarmaPbxData = null;
        if ($is_zadarma_enabled) {
            $zadarmaPbxData = Cache::remember('zadarma_pbx_data_d' . session()->get('division.id') . '_' . Auth::id(), now()->addHours($this->cacheH), function () {
                return (new PBXController())->getUserPBXData();
            });
        }
        $view->with('zadarma_user_pbxdata', $zadarmaPbxData);

        // Check Gmail Settings. Not perfect place
        $last_user_sync = request()->session()->get('last_user_sync');
        if (!$last_user_sync || $last_user_sync->lte(now()->subMinutes(15)->toDateTimeString())) {
            (new GMailController())->checkBrokenAccounts();
            request()->session()->put('last_user_sync', now());
        }

        // cache reset added to employee save
        $view->with('is_zadarma_enabled', $is_zadarma_enabled);
        $view->with('nav_divisions', $data);
    }
}
