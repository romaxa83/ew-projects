<?php

namespace App\GraphQL\Mutations\FrontOffice\Members;

use App\Exceptions\Auth\TokenDecryptException;
use App\GraphQL\Types\NonNullType;
use App\Models\Dealers\Dealer;
use App\Models\Localization\Translate;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Services\Dealers\DealerVerificationService;
use App\Services\Technicians\TechnicianVerificationService;
use App\Services\Users\UserVerificationService;
use App\Traits\Auth\EmailCryptToken;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class MemberEmailConfirmationMutation extends BaseMutation
{
    use EmailCryptToken;

    public const NAME = 'memberEmailConfirmation';

    public function type(): Type
    {
        return Type::boolean();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return true;
    }

    public function args(): array
    {
        return [
            'token' => NonNullType::string(),
        ];
    }

    /**
     * @throws Exception
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        try {
            $decrypt = $this->decryptEmailToken($args['token']);
        } catch (TokenDecryptException $e) {
            throw new TranslatedException(
                _t(
                    Translate::SITE_PLACE,
                    'email_verification__link_is_invalid'
                )
            );
        }

        if ($decrypt->guard === User::GUARD) {
            $service = resolve(UserVerificationService::class);
            $user = User::query()->findOrFail($decrypt->id);
        } elseif ($decrypt->guard === Technician::GUARD) {
            $service = resolve(TechnicianVerificationService::class);
            $user = Technician::query()->findOrFail($decrypt->id);
        } elseif ($decrypt->guard === Dealer::GUARD) {
            $service = resolve(DealerVerificationService::class);
            $user = Dealer::query()->findOrFail($decrypt->id);
        } else {
            return false;
        }

        return $service->verifyEmailByCode($user, $decrypt->code);
    }

    protected function rules(array $args = []): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
