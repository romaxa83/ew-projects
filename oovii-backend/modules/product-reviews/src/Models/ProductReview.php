<?php

namespace WezomCms\ProductReviews\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\Contracts\ReviewRatingInterface;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Contracts\BelongsToAdminInterface;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Scopes\BelongsToAdminScope;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Users\Models\User;

/**
 * \WezomCms\ProductReviews\Models\ProductReview
 *
 * @property int $id
 * @property bool $published
 * @property int|null $product_id
 * @property int|null $parent_id
 * @property int|null $user_id
 * @property bool $already_bought
 * @property bool $admin_answer
 * @property int $rating
 * @property bool $like
 * @property string|null $name
 * @property string|null $email
 * @property string $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_date
 * @property-read ProductReview|null $parent
 * @property-read Product|null $product
 * @property-read User|null $user
 *
 * @see ProductReview::administrator()
 * @property-read Administrator|null $administrator
 *
 * @see ProductReview::children()
 * @property-read Collection|ProductReview[] $children
 *
 * @see ProductReview::publishedChildren()
 * @property-read Collection|ProductReview[] $publishedChildren
 *
 * @method static Builder|ProductReview filter(array $input = [], $filter = null)
 * @method static Builder|ProductReview newModelQuery()
 * @method static Builder|ProductReview newQuery()
 * @method static Builder|ProductReview onlyChildren()
 * @method static Builder|ProductReview paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|ProductReview published()
 * @method static Builder|ProductReview publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|ProductReview query()
 * @method static Builder|ProductReview root()
 * @method static Builder|ProductReview simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|ProductReview top()
 * @method static Builder|ProductReview whereAdminAnswer($value)
 * @method static Builder|ProductReview whereAlreadyBought($value)
 * @method static Builder|ProductReview whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|ProductReview whereCreatedAt($value)
 * @method static Builder|ProductReview whereEmail($value)
 * @method static Builder|ProductReview whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|ProductReview whereId($value)
 * @method static Builder|ProductReview whereLike(string $column, string $value, string $boolean = 'and')
 * @method static Builder|ProductReview whereName($value)
 * @method static Builder|ProductReview whereParentId($value)
 * @method static Builder|ProductReview whereProductId($value)
 * @method static Builder|ProductReview wherePublished($value)
 * @method static Builder|ProductReview whereRating($value)
 * @method static Builder|ProductReview whereText($value)
 * @method static Builder|ProductReview whereUpdatedAt($value)
 * @method static Builder|ProductReview whereUserId($value)
 * @mixin Eloquent
 */
class ProductReview extends Model implements ReviewRatingInterface, BelongsToAdminInterface
{
    use Filterable;
    use PublishedTrait;
    use HasFactory;

    public const TABLE = 'product_reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published',
        'product_id',
        'parent_id',
        'user_id',
        'already_bought',
        'admin_answer',
        'rating',
        'like',
        'name',
        'email',
        'text',
        'created_at',
    ];

    protected $casts = [
        'published' => 'bool',
        'already_bought' => 'bool',
        'admin_answer' => 'bool',
        'like' => 'bool'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BelongsToAdminScope());
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function administrator(): HasOneThrough
    {
        return $this->hasOneThrough(
            Administrator::class,
            Product::class,
            Product::TABLE . '.id',
            Administrator::TABLE . '.id',
            'product_id',
            'provider_id',
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function publishedChildren(): HasMany
    {
        return $this->children()->published()->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        return sprintf(
            '%d %s %d',
            $this->created_at->format('d'),
            $this->created_at->getTranslatedMonthName('Do MMMM'),
            $this->created_at->format('Y')
        );
    }

    public function getNameAttribute($value): ?string
    {
        return $this->user->name ?? $value;
    }

    public function getEmailAttribute($value): ?string
    {
        return $this->user->email ?? $value;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param  int  $rating
     * @return $this
     */
    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        $this->save();

        return $this;
    }

    public function isRoot(): bool
    {
        return !$this->parent_id && !$this->admin_answer;
    }

    /**
     * @param $query
     */
    public function scopeRoot($query): void
    {
        $query->whereNull('parent_id')->where('admin_answer', false);
    }

    /**
     * @param $query
     */
    public function scopeOnlyChildren($query): void
    {
        $query->whereNotNull('parent_id');
    }

    public function getReviewFullName(): string
    {
        return $this->admin_answer && !$this->name
            ? __('cms-product-reviews::admin.Site administration')
            : sprintf('%s %s (%s)', $this->name, $this->email, $this->created_at->format('d.m.Y'));
    }
}
