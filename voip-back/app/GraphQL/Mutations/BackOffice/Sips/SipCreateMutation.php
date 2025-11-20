<?php

namespace App\GraphQL\Mutations\BackOffice\Sips;

use App\Dto\Sips\SipDto;
use App\GraphQL\InputTypes\Sips\SipInput;
use App\GraphQL\Types\Sips\SipType;
use App\Models\Sips\Sip;
use App\Permissions;
use App\Rules\PasswordRule;
use App\Services\Sips\SipService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SipCreateMutation extends BaseMutation
{
    public const NAME = 'SipCreate';
    public const PERMISSION = Permissions\Sips\CreatePermission::KEY;

    public function __construct(
        protected SipService $service
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null)
    : bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'input' => SipInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return SipType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Sip
    {
        return makeTransaction(
            fn(): Sip => $this->service->create(
                SipDto::byArgs($args['input'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.number' => ['nullable', 'numeric', 'digits_between:3,10', Rule::unique(Sip::class, 'number')],
            'input.password' => ['required', 'string', new PasswordRule(Sip::MIN_LENGTH_PASSWORD)],
        ];
    }
}

