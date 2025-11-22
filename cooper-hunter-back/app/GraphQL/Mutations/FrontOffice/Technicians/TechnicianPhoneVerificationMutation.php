<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\Services\Technicians\TechnicianService;
use App\Traits\Auth\SmsConfirmable;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TechnicianPhoneVerificationMutation extends BaseMutation
{
    use SmsConfirmable;

    public const NAME = 'technicianPhoneVerification';

    public function __construct(protected TechnicianService $service)
    {
        $this->setTechnicianGuard();
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
