<?php

namespace App\GraphQL\Queries\FrontOffice\Members;

use App\Contracts\Members\Member;
use App\GraphQL\Types\Members\MemberProfileUnionType;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class MemberProfileUnionQuery extends BaseQuery
{
    public const NAME = 'memberProfile';
    public const DESCRIPTION = 'Authorization Bearer should be provided';

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return MemberProfileUnionType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Member
    {
        return $this->user();
    }
}
