<?php

namespace WezomCms\Catalog\Models\Collections;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * @property int $id
 * @property bool $published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin CollectionTranslation
 */
class Category extends EloquentModel
{
    use Translatable;
    use HasFactory;
    use GetForSelectTrait;
    use PublishedTrait;
    use OrderBySort;

    public const TABLE = 'collection_categories';
    protected $table = self::TABLE;

    protected $fillable = [
        'published',
        'sort'
    ];

    protected $translatedAttributes = ['name'];

    protected $casts = [
        'published' => 'bool',
    ];

    protected $with = ['translation'];

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public static function getForSelect(callable $callback = null): array
    {
        $query = static::select()
            ->sorting();

        if (null !== $callback) {
            $callback($query);
        }

        $tree = Helpers::groupByParentId($query->get());

        return static::addTreeSpaces($tree);
    }

    private static function addTreeSpaces(array $tree, $id = null, array &$result = [], string $space = ''): array
    {
        foreach ($tree[$id] ?? [] as $group) {
            if (isset($tree[$group->id])) {
                $result[$group->id] = ['disabled' => true, 'name' => $space . $group->name];
                static::addTreeSpaces($tree, $group->id, $result, $space . '&nbsp;&nbsp;&nbsp;&nbsp;');
            } else {
                $result[$group->id] = ['name' => $space . $group->name];
            }
        }

        return $result;
    }
}

