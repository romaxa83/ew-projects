<?php


namespace App\Dto\Alerts\Fcm;


use App\Models\Alerts\Alert;
use Illuminate\Support\Facades\App;

/**
 * Class FcmData
 * @package App\Dto\Alerts\Fcm
 *
 * @property-read string $type
 * @property-read string $title
 * @property-read string $body
 * @property-read array $data
 */
class FcmData
{
    private string $type;
    private string $title;
    private string $body;
    private array $data;

    public static function init(Alert $alert): self
    {
        $fcm = new self();

        $fcm->type = $alert->model_type;

        $locale = App::getLocale();

        if (!$locale) {
            $locale = config('app.locale');
        }

        $fcm->title = (string)trans(
            $alert->title,
            self::getReplace(
                $alert->meta,
                'title',
                $locale
            ),
            $locale
        );

        $fcm->body = (string)trans(
            $alert->description,
            self::getReplace(
                $alert->meta,
                'description',
                $locale
            ),
            $locale
        );

        $fcm->data = [
            'name' => (string)$alert->model_type,
            'id' => (string)$alert->model_id
        ];

        return $fcm;
    }

    private static function getReplace(?array $meta, string $field, string $language): array
    {
        if (empty($meta) || empty($meta[$field])) {
            return [];
        }
        return array_map(
            fn($item) => trans(key: $item, locale: $language),
            $meta[$field]
        );
    }

    public function __get(string $name): mixed
    {
        return property_exists($this, $name) ? $this->{$name} : null;
    }
}
