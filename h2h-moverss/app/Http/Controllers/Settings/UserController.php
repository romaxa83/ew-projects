<?php

namespace App\Http\Controllers\Settings;

use App\Models\User\Role;
use App\Models\User\RouteList;
use App\DataTables\Settings\User\{UsersDataTable, UsersDataTableEditor};
use App\Http\Controllers\Controller;
use Illuminate\Http\{JsonResponse, Request};
use App\User;
use Auth, DB, Exception;

class UserController extends Controller
{
    public function usersAjax(User $user, Request $request): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'records' => $user->userDetails()->get(),
                'whoami' => [
                    'uid' => Auth::id(),
                    'is_admin' => Auth::user()->isAdmin(),
                    'division' => [
                        'ids' => $request->session()->get('division.allowed'),
                        'is_multiple' => $request->session()->get('division.is_multiple'),
                    ],
                ],
            ]);
    }

    public function usersRecords(UsersDataTable $dataTable)
    {
        return $dataTable->render('layouts.home.dt');
    }

    public function usersDtEditor(UsersDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function ajaxInfo(): JsonResponse
    {
        $records = RouteList::with('groups')->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
                'types' => [
                    'roles' => Role::get(['id', 'title'])->keyBy('id')
                ],
            ]);
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:users_routes_list',
            'title' => 'nullable|string|max:100',
            'groups_checked.*' => 'nullable|exists:users_roles,id',
        ]);


        $record = RouteList::with('groups')->findOrFail($request->id);

        try {
            DB::transaction(function () use ($record, $request) {
                if ($record->title !== $request->title) {
                    $record->title = strip_tags($request->title);
                    $record->save();
                }

                $record->groups()->sync((array) $request->groups_checked);
            });
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        $record = RouteList::with('groups')->find($request->id);
        return response()
            ->json([
                'success' => true,
                'record' => $record,
            ]);
    }

}
