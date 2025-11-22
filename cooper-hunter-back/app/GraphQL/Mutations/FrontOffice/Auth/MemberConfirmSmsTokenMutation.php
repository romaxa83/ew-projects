<?php

namespace App\GraphQL\Mutations\FrontOffice\Auth;

use App\Dto\Auth\SmsConfirmTokenDto;
use App\Entities\Auth\PhoneTokenEntity;
use App\GraphQL\InputTypes\Auth\Sms\ConfirmSmsTokenInput;
use App\GraphQL\Types\Auth\Sms\SmsAccessTokenType;
use App\Rules\Auth\SmsConfirmationTokenRule;
use App\Services\Auth\PhoneAuthService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberConfirmSmsTokenMutation extends BaseMutation
{
    public const NAME = 'memberConfirmSmsToken';

    public function __construct(protected PhoneAuthService $service)
    {
    }

    public function args(): array
    {
        return [
            'sms' => [
                'type' => ConfirmSmsTokenInput::nonNullType(),
            ]
        ];
    }

    public function type(): Type
    {
        return SmsAccessTokenType::type();
    }

    /** @throws Throwable */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): PhoneTokenEntity {
        return makeTransaction(
            fn() => $this->service->confirmSmsToken(
                SmsConfirmTokenDto::byArgs($args['sms'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'sms' => [new SmsConfirmationTokenRule()],
        ];
    }
}
