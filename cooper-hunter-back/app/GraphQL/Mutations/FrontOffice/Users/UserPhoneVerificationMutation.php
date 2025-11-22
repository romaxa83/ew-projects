<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Services\Users\UserService;
use App\Traits\Auth\SmsConfirmable;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserPhoneVerificationMutation extends BaseMutation
{
    use SmsConfirmable;

    public const NAME = 'userPhoneVerification';

    public function __construct(protected UserService $service)
    {
        $this->setUserGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->getAuthGuard()->check();
    }

    public function args(): array
    {
        return $this->smsAccessTokenArg();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->confirmPhone($this->user(), $args['sms_access_token'])
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->smsAccessTokenRule(true);
    }
}
