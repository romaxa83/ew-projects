<?php

namespace App\Rules;

use App\Contracts\Members\Member;
use App\Models\BaseModel;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseBelongsToMemberRule implements Rule
{
    /**
     * If a relationship name has been specified, the user will be checked against the associated model.
     */
    protected string $through;

    protected string $morphToField = 'member';

    protected string|BaseModel $model;

    protected string $key = 'id';

    public function __construct(protected ?Member $member = null)
    {
    }

    public static function forMember(Member $member): static
    {
        return new static($member);
    }

    public function passes($attribute, $value): bool
    {
        $builder = $this->getQuery()->where($this->key, $value);

        if (isset($this->through) && $this->through) {
            $this->resolveThrough($builder);
        } elseif ($this->member) {
            $this->resolveWhereUser($builder);
        }

        return $builder->exists();
    }

    protected function getQuery(): Builder
    {
        return $this->model::query();
    }

    private function resolveThrough(Builder $builder): void
    {
        $builder->whereHas(
            $this->through,
            function (Builder $has) {
                $this->resolveWhereUser($has);
            }
        );
    }

    private function resolveWhereUser(Builder $builder): void
    {
        $builder->where($this->morphCondition());
    }

    private function morphCondition(): array
    {
        return [
            $this->morphToField . '_type' => $this->member->getMorphType(),
            $this->morphToField . '_id' => $this->member->getId(),
        ];
    }

    public function message(): string
    {
        return __('validation.exists');
    }
}
