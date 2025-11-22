<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\FrontOffice\Members;

use App\GraphQL\Types\NonNullType;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Notifications\Members\MemberForgotPasswordVerification;
use App\Services\Dealers\DealerVerificationService;
use App\Services\Technicians\TechnicianVerificationService;
use App\Services\Users\UserVerificationService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Rebing\GraphQL\Support\SelectFields;

use function app;

class MemberForgotPasswordMutation extends BaseMutation
{
    public const NAME = 'memberForgotPasswordMutation';
    public const DESCRIPTION = 'Метод для отправки ссылки для сброса пароля. На почту клиента приходит ссылка в виде {link}/{token}, {link}.';

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'email' => [
                'type' => NonNullType::string(),
            ],
        ];
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->guest();
    }

    /**
     * @throws Exception
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        if ($user = Technician::query()
            ->where('email', $args['email'])
            ->first()) {
            $service = resolve(TechnicianVerificationService::class);
        } elseif ($user = Dealer::query()->where('email', $args['email'])->first()){
            $service = resolve(DealerVerificationService::class);
        } else {
            $user = User::query()
                ->where('email', $args['email'])
                ->firstOrFail();

            $service = resolve(UserVerificationService::class);
        }

        Notification::route('mail', $user->getEmailString())
            ->notify(
                (new MemberForgotPasswordVerification(
                    $user,
                    $service->getLinkForEmailReset($user)
                ))
                    ->locale(app()->getLocale())
            );

        return true;
    }

    protected function rules(array $args = []): array
    {
        if (Technician::query()->where('email', $args['email'])->exists()) {
            $this->setTechnicianGuard();
            $rule = 'exists:technicians,email';
        } elseif (Dealer::query()->where('email', $args['email'])->exists()) {
            $this->setDealerGuard();
            $rule = 'exists:dealers,email';
        }
        else {
            $this->setUserGuard();
            $rule = 'exists:users,email';
        }

        return [
            'email' => 'required|email:filter|' . $rule,
        ];
    }
}
