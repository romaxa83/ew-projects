<?php


namespace App\Services\Fcm;


use App\Exceptions\Fcm\FcmTokenException;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class FcmService
{
    public function saveToken(User|Technician $member, string $token): void
    {
        $this->checkToken($token);

        $member->fcmTokens()
            ->updateOrCreate(
                [
                    'token' => $token
                ]
            );
    }

    private function checkToken(string $token): void
    {
        $apiUrl = sprintf(
            config('firebase.api_url'),
            config('firebase.default')
        );


        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->getOAuthToken()
            ]
        )
            ->post(
                $apiUrl,
                [
                    'validateOnly' => true,
                    'message' => [
                        'token' => $token
                    ]
                ]
            );

        if ($response->status() !== Response::HTTP_OK) {
            throw new FcmTokenException();
        }
    }

    private function getOAuthToken(): string
    {
        $authToken = resolve(
            ServiceAccountCredentials::class,
            [
                'scope' => config('firebase.scopes'),
                'jsonKey' => base_path(
                    config('firebase.projects.' . config('firebase.default') . '.credentials.file')
                )
            ]
        );
        return $authToken->fetchAuthToken()['access_token'];
    }
}
