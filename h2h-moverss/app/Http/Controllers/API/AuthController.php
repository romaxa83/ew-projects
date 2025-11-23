<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Profile\ProfileResource;
use Illuminate\Http\{JsonResponse, Request};
use Auth, Exception;

class AuthController extends Controller
{

    public function profile(): ProfileResource
    {
        $user = Auth::user();
        $user->load([
            'employee.emails',
            'employee.phones',
            'employee.cashRegistry'
        ]);

        return ProfileResource::make($user);
    }


    /**
     * Login for mobile section.
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!Auth::attempt($credentials)) {
                throw new Exception('Invalid login or PWD');
            }

            // Authentication passed...
            $user = Auth::user();

            if (!$user->active) {
                throw new Exception('Disabled user');
            }

            $user->load('tokens');

            if ($user->tokens) {
                $user->tokens()->delete(); // todo remove only mobile tokens
            }

            $token = $user->createToken('MobileApp', ['mobile']);

            return response()
                ->json([
                    'token' => $token->plainTextToken,
                ]);
        } catch (Exception $e) {
            return response()
                ->json([
                    'msg' => $e->getMessage(),
                ], 422);
        }
    }
}

