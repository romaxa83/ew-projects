<?php

namespace App\GraphQL\Mutations\BackOffice\Localization;

use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateDeletePermission;
use App\Rules\ExistsLanguages;
use App\Traits\Localization\LocalizationCacheTags;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class DeleteTranslateMutation extends BaseMutation
{
    use LocalizationCacheTags;

    public const NAME = 'translationDelete';
    public const PERMISSION = TranslateDeletePermission::KEY;

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
            'lang' => Type::nonNull(Type::string()),
        ];
    }

    /**
     * @throws Exception
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        Translate::flushQueryCache($this->getCacheTags($args));

        return (bool)Translate::query()->where($args)->delete();
    }

    protected function rules(array $args = []): array
    {
        return [
            'place' => ['required', 'string', 'min:3'],
            'key' => ['required', 'string', 'min:3'],
            'lang' => ['required', 'string', 'min:2', 'max:3', new ExistsLanguages()],
        ];
    }
}
