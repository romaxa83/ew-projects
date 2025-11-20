<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Languages
 *
 * @property int $id
 * @property string $name
 * @property string $native
 * @property string $slug
 * @property string $locale
 * @property boolean $default
 */

class Languages extends Model
{
    const DEFAULT = 'en';

    protected $table = 'languages';

    protected $casts = [
        'default' => 'boolean'
    ];

    public function isDefault(): bool
    {
        return $this->slug === self::DEFAULT;
    }

    public static function getLang(): string
    {
        return \App::getLocale() ?? self::DEFAULT;
    }
}
