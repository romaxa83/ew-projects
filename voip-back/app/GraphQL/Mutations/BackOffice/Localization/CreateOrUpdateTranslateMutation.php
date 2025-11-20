<?php

namespace App\GraphQL\Mutations\BackOffice\Localization;

use App\Models\Localization\Translation;
use App\Permissions\Localization\TranslateUpdatePermission;
use App\Rules\ExistsLanguages;
use App\Traits\Localization\LocalizationCacheTags;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class CreateOrUpdateTranslateMutation extends BaseMutation
{
    use LocalizationCacheTags;

    public const NAME = 'CreateOrUpdateTranslate';
    public const PERMISSION = TranslateUpdatePermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'place' => Type::nonNull(Type::string()),
            'key' => Type::nonNull(Type::string()),
            'text' => Type::string(),
            'lang' => Type::nonNull(Type::string()),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        Translation::flushQueryCache($this->getCacheTags($args));

        return (bool)Translation::query()->upsert($args, ['place', 'key', 'lang']);
    }

    protected function rules(array $args = []): array
    {
        return [
            'place' => ['required', 'string', 'min:3'],
            'key' => ['required', 'string', 'min:3'],
            'text' => ['nullable'],
            'lang' => ['required', 'string', 'min:2', 'max:3', new ExistsLanguages()],
        ];
    }

}
