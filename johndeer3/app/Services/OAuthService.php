<?php
//
//namespace App\Services;
//
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;
//
//class OAuthService
//{
//    private $clientSecret;
//    private $clientId;
//
//    /**
//     * OAuthService constructor.
//     * @throws \Exception
//     */
//    public function __construct()
//    {
//        $this->clientSecret = config('auth.oauth_secret_key');
//        $this->clientId = config('auth.oauth_secret_id');
//    }
//
//    /**
//     * @param $password
//     * @param $email
//     * @return mixed
//     * @throws \Exception
//     */
//    public function getBearerToken($password, $email)
//    {
//        $params = [
//            'grant_type' => 'password',
//            'client_id' => $this->clientId,
//            'client_secret' => $this->clientSecret,
//            'username' => $email,
//            'password' => $password,
//            'scope' => '*',
//        ];
//
//        return $this->oauthResponse($params);
//    }
//
//    /**
//     * @param $refreshToken
//     * @return mixed
//     * @throws \Exception
//     */
//    public function getRefreshToken($refreshToken)
//    {
//        $params = [
//            'grant_type' => 'refresh_token',
//            'refresh_token' => $refreshToken,
//            'client_id' => $this->clientId,
//            'client_secret' => $this->clientSecret,
//            'scope' => '*'
//        ];
//
//        return $this->oauthResponse($params);
//    }
//
//    /**
//     * @param $params
//     * @return mixed
//     * @throws \Exception
//     */
//    private function oauthResponse($params)
//    {
//        try {
//            request()->request->add($params);
//
//            $tokenRequest = Request::create(route('passport.token'), Request::METHOD_POST);
//            $content = (string)Route::dispatch($tokenRequest)->getContent();
//
//            return json_decode($content, true);
//        } catch (\Exception $exception) {
//
//            throw $exception;
//        }
//    }
//}
