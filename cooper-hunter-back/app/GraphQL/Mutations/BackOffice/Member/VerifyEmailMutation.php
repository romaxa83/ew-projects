<?php

namespace App\GraphQL\Mutations\BackOffice\Member;

use App\Enums\Member\MemberEnum;
use App\Factories\MemberFactory;
use App\GraphQL\Types\Enums\Members\MemberTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Permissions\Members\MemberVerifyEmailPermission;
use Carbon\CarbonImmutable;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VerifyEmailMutation extends BaseMutation
{
    public const NAME = 'memberVerifyEmail';
    public const PERMISSION = MemberVerifyEmailPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required'],
            ],
            'member' => [
                'type' => MemberTypeEnum::type(),
                'rules' => ['required', 'string']
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
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
    ): bool
    {
        $factory = new MemberFactory(MemberEnum::fromValue($args['member']));

        $repo = $factory->getRepo();
        /** @var $member User|Dealer|Technician */
        $member = $repo->getBy('id', $args['id'], [], true);

        return $member->update(['email_verified_at' => CarbonImmutable::now()]);
    }
}

