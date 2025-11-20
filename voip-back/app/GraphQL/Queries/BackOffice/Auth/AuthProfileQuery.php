<?php

namespace App\GraphQL\Queries\BackOffice\Auth;

use App\GraphQL\Types\Auth\AuthProfileType;
use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Models\Employees\Employee;
use App\Repositories\Admins\AdminRepository;
use App\Repositories\Employees\EmployeeRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use Illuminate\Support\Facades\DB;
use GraphQL\Type\Definition\{ResolveInfo, Type};
use Rebing\GraphQL\Support\SelectFields;

class AuthProfileQuery extends BaseQuery
{
    public const NAME = 'AuthProfile';

    public function __construct()
    {
        $this->setAuthGuard();
    }

//    public function authorize(
//        mixed $root,
//        array $args,
//        mixed $ctx,
//        ResolveInfo $info = null,
//        Closure $fields = null
//    ): bool
//    {
//        dd($this->authCheck());
//        return $this->authCheck();
//    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return AuthProfileType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): BaseAuthenticatable
    {
        return $this->getUserByAccessToken();
    }

    public function getUserByAccessToken(): null|Admin|Employee
    {
        dd(app()->getLocale());
        logger_info('HEADERS', $_SERVER);
        logger_info('HEADERS_LIST', headers_list());
        $accessTokenData = data_get(getallheaders(), 'Authorization');

        if(!$accessTokenData){
            throw new \InvalidArgumentException(__('no authorization header'));
        }
        $accessToken = explode(' ', $accessTokenData);
        $tokenParts = explode('.' , $accessToken[1]);

        if(!isset($tokenParts[1])){
            throw new \InvalidArgumentException(__('error.invalid access token'));
        }
        $parsToken = jsonToArray(base64_decode($tokenParts[1]));

        if(!isset($parsToken['jti'])){
            throw new \InvalidArgumentException(__('error.invalid access token'));
        }

        $userRecord = DB::table('oauth_access_tokens')->where('id', $parsToken['jti'])->first();

        if(!$userRecord){
            throw new \DomainException(__('error.not found record by access token'));
        }

        $oauthClient = DB::table('oauth_clients')
            ->select('provider')
            ->where('id', $userRecord->client_id)->first();

        if(!$oauthClient){
            throw new \DomainException(__('not found oauthClient'));
        }

        $repo = null;
        if($oauthClient->provider === Employee::TABLE){
            $repo = resolve(EmployeeRepository::class);
        }
        if($oauthClient->provider === Admin::TABLE){
            $repo = resolve(AdminRepository::class);
        }
        if(!$repo){
            throw new \DomainException(__('not created repositories'));
        }

        return $repo->getBy('id', $userRecord->user_id);
    }
}
