<?php

namespace App\GraphQL\Mutations\FrontOffice\Auth;

use App\Entities\Auth\PhoneTokenEntity;
use App\GraphQL\Types\Auth\Sms\SmsCodeTokenType;
use App\GraphQL\Types\NonNullType;
use App\Rules\PhoneRule;
use App\Services\Auth\PhoneAuthService;
use App\ValueObjects\Phone;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class MemberRequestSmsTokenMutation extends BaseMutation
{
    public const NAME = 'memberRequestSmsToken';

    public function __construct(protected PhoneAuthService $service)
    {
        $this->setMemberGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck($this->guard);
    }

    public function args(): array
    {
        return [
            'phone_number' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function type(): Type
    {
        return SmsCodeTokenType::type();
    }

    /** @throws Exception */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): PhoneTokenEntity {
        $phone = new Phone($args['phone_number']);

        return $this->service->sendPhoneCode($phone);
    }

    protected function rules(array $args = []): array
    {
        return [
            'phone_number' => ['required', new PhoneRule()],
        ];
    }
}
