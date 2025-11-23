<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Zadarma\PBXController;
use App\Models\Employee;
use App\User;
use Auth, Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function getUserEnvironment(Request $request)
    {
        try {
            $response = ['success' => false];
            try {
                $PBXController = new PBXController;
                $zadarma = ['hasApi' => null, 'hasExtension' => null];
                $zadarma['hasApi'] = $PBXController->hasZadarma();
                $zadarma['hasExtension'] = $PBXController->getUserPBXExtension();

            } catch (Exception $e) {
            }

            $response = [
                'user' => User::select(['id', 'name', 'email', 'active', 'division_ids'])->findOrFail(Auth::id()),
                'employer' => Employee::where('auth_user_id', Auth::id())->firstOrFail(['id', 'name', 'l_name', 'division_ids']),
                'divisionID' => session()->get('division.id'),
                'zadarma' => $zadarma
            ];
            if ($request->has('page') && $request->page == 'communications') {
                $response['responsibles'] = Employee::where('active', 1)
                    ->whereHas('user.roles', function ($q) {
                        return $q->whereIn('users_roles.id', [1, 5]); // IN: Admin, Manager
                    })
                    ->orderBy('id', 'DESC')
                    ->whereJsonContains('division_ids', request()->session()->get('division.id'))
                    ->selectRaw('`id` as value, CONCAT(`name`, " ", `l_name`) as label')
                    ->get();
            }

            $response['success'] = true;
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return response()
            ->json($response);
    }
}
