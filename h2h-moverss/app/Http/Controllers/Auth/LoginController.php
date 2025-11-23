<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Zadarma\PBXController;
use App\Models\Division;
use App\Models\Employee;
use App\Providers\RouteServiceProvider;
use App\User;
use Auth, Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')
            ->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    public function authenticated(Request $request, $user)
    {
        $this->lastLogon($request, $user);
        $this->setProjectSession($request, $user);
    }

    /**
     * Saving Auth history.
     * @param Request $request
     * @param mixed $user
     * @param int|null $max_records Max History records
     */
    public function lastLogon(Request $request, $user, ?int $max_records = 5): void
    {
        $miscs = $user->miscs;

        $r = [
            'ip' => $request->ip(),
            'date' => now()->toDateTimeString(),
            'agent' => strip_tags($request->header('User-Agent')),
        ];

        $miscs['auth']['log'][] = $r;
        $co = count($miscs['auth']['log']);
        if ($co > $max_records) {
            $miscs['auth']['log'] = array_reverse($miscs['auth']['log']);
            $miscs['auth']['log'] = array_slice($miscs['auth']['log'], 0, $max_records);
            $miscs['auth']['log'] = array_reverse($miscs['auth']['log']);
        }

        $user->miscs = $miscs;
        $user->save();
    }

    public function setProjectSession(Request $request, $user): void
    {
        if (!empty($user->division_ids))
            $is_multiple = count($user->division_ids) > 1;
        $id = current($user->division_ids);

        if ($is_multiple && !empty($user->miscs['division']['id'])) {
            $id = $user->miscs['division']['id'];
        }

        $divisionData = Division::findOrFail($id);

        $request->session()->put('division.id', $id);
        $request->session()->put('division.is_multiple', $is_multiple);
        $request->session()->put('division.allowed', $user->division_ids);
        $request->session()->put('division.miscs', $divisionData->miscs);

        // setPbxData
        $PBXController = new PBXController();
        $is_zadarma_enabled = $PBXController->isAllowedCallWidget();
        Cache::put('is_zadarma_enabled_d' . $id . '_' . Auth::id(), $is_zadarma_enabled, now()->addHours(6));
        Cache::put('zadarma_pbx_data_d' . $id . '_' . Auth::id(),  $is_zadarma_enabled ? $PBXController->getUserPBXData() : null, now()->addHours(6));

    }
}
