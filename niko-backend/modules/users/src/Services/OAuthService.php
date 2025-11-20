<?php

namespace WezomCms\Users\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use WezomCms\Users\Models\User;

class OAuthService
{
    private $oClient;

    public function __construct()
    {
        $this->oClient = \DB::table('oauth_clients')
            ->where('password_client', 1)
            ->first();
    }

    public function getBearerToken($email)
    {
        $params = [
            'grant_type' => 'password',
            'client_id' => $this->oClient->id,
            'client_secret' => $this->oClient->secret,
            'username' => $email,
            'password' => User::DEFAULT_PASSWORD,
            'scope' => '*',
        ];

        return $this->oauthRequest($params);
    }

    public function getRefreshToken($refreshToken)
    {
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->oClient->id,
            'client_secret' => $this->oClient->secret,
            'scope' => ''
        ];

        return $this->oauthRequest($params);
    }

    public function oauthRequest(array $params)
    {
        $headers = array(
            'Content-type: Application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, route('passport.token'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $output = curl_exec($ch);

        $error = curl_error($ch);

        curl_close($ch);

        return json_decode((string) $output, true);
    }
}
