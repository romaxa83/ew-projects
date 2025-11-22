<?php

namespace App\GraphQL\Mutations\BackOffice\Localization;

use App\GraphQL\Types\FileType;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateUpdatePermission;
use App\Traits\Localization\LocalizationCacheTags;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use JsonException;
use Rebing\GraphQL\Support\SelectFields;

class UpsertTranslationsMutation extends BaseMutation
{
    use LocalizationCacheTags;

    public const NAME = 'translationsUpsert';
    public const PERMISSION = TranslateUpdatePermission::KEY;
    public const DESCRIPTION = 'Translations with version=1 are only allowed';

    protected const VERSION = 1;

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
            'json' => [
                'type' => FileType::nonNullType(),
                'rules' => ['required', 'file', 'mimetypes:application/json'],
            ],
        ];
    }

    /**
     * @throws JsonException
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        Translate::flushQueryCache($this->getCacheTags($args));

        $array = jsonToArray($args['json']->getContent());

        if ($array['version'] !== self::VERSION) {
            throw new TranslatedException('Json file version is not allowed');
        }

        $languages = array_flip(languages()->pluck('slug')->toArray());

        foreach (array_chunk($array['data'], 500) as $chunk) {
            $upsert = [];
            foreach ($chunk as $item) {
                $locales = array_intersect_key($item, $languages);

                foreach ($locales as $locale => $text) {
                    $upsert[] = [
                        'place' => $item['place'],
                        'key' => $item['key'],
                        'text' => $text,
                        'lang' => $locale,
                    ];
                }
            }

//            Translate::query()->upsert($upsert, ['place', 'key', 'lang']);
            Translate::query()->insertOrIgnore($upsert);
        }

        Translate::flushQueryCache($this->getCacheTags($args));

        return true;
    }

}
