<?php

declare(strict_types=1);

namespace Wezom\Core\Models;

use BenSampo\Enum\Enum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Wezom\Core\Database\Factories\TranslationFactory;
use Wezom\Core\Dto\FilteringDto;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\Traits\Model\Filterable;

/**
 * \Wezom\Core\Models\Translation
 *
 * @property int $id
 * @property string|null $namespace
 * @property Enum $side
 * @property string $key
 * @property string $language
 * @property string|null $text
 * @property bool $translated
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static TranslationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Translation filter(array $input = [], $filter = null)
 * @method static Builder<static>|Translation filterWithOrder(FilteringDto $filtering)
 * @method static Builder<static>|Translation newModelQuery()
 * @method static Builder<static>|Translation newQuery()
 * @method static Builder<static>|Translation paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder<static>|Translation query()
 * @method static Builder<static>|Translation simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder<static>|Translation whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder<static>|Translation whereCreatedAt($value)
 * @method static Builder<static>|Translation whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder<static>|Translation whereId($value)
 * @method static Builder<static>|Translation whereKey($value)
 * @method static Builder<static>|Translation whereLanguage($value)
 * @method static Builder<static>|Translation whereLike($column, $value, $boolean = 'and')
 * @method static Builder<static>|Translation whereNamespace($value)
 * @method static Builder<static>|Translation whereSide($value)
 * @method static Builder<static>|Translation whereText($value)
 * @method static Builder<static>|Translation whereTranslated($value)
 * @method static Builder<static>|Translation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Translation extends Model
{
    use Filterable;
    use HasFactory;

    protected $fillable = [
        'namespace',
        'side',
        'key',
        'language',
        'text',
    ];
    protected $casts = [
        'translated' => 'boolean',
        'side' => TranslationSideEnum::class,
    ];
}
