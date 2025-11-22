<?php

namespace App\GraphQL\Mutations\Common\Localization;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Rules\ExistsLanguages;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class SetLanguageMutation extends BaseMutation
{
    public const NAME = 'setTranslation';

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->authCheck([User::GUARD, Admin::GUARD]);
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'lang' => NonNullType::string(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $lang = $args['lang'];

        if ($this->authCheck(User::GUARD) && $user = $this->user(User::GUARD)) {
            $user->lang = $lang;
            $user->save();

            return true;
        }

        if ($this->authCheck(Admin::GUARD) && $admin = $this->user(Admin::GUARD)) {
            $admin->lang = $lang;
            $admin->save();

            return true;
        }

        return false;
    }

    protected function rules(array $args = []): array
    {
        return [
            'lang' => ['required', 'string', 'min:2', 'max:3', new ExistsLanguages()],
        ];
    }

}
