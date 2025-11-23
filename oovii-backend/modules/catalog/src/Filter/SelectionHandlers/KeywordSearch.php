<?php

namespace WezomCms\Catalog\Filter\SelectionHandlers;

use Illuminate\Database\Query\Builder;
use Str;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\ResultFilteringInterface;
use WezomCms\Core\Foundation\Helpers;

class KeywordSearch implements ResultFilteringInterface
{
    /**
     * @var string|null
     */
    private $sentence;

    /**
     * @var array
     */
    private $words;

    /**
     * KeywordSearch constructor.
     * @param $sentence
     */
    public function __construct($sentence = null)
    {
        $this->sentence = $sentence;

        $this->words = $this->clearSentence($this->sentence);
    }

    /**
     * @param  Builder|mixed  $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        $this->apply($queryBuilder, $filter, $criteria);
    }

    /**
     * @param  Builder|mixed  $queryBuilder
     * @param  FilterInterface|null  $filter
     * @param  array  $criteria
     */
    public function apply($queryBuilder, FilterInterface $filter = null, array $criteria = [])
    {
        if ($this->hasWords() === false) {
            $queryBuilder->whereRaw('1 = 0');
            return;
        }

        $queryBuilder->published()
            ->whereHas('translations', function ($query) {
                $query->where(function ($query) {
                    $query->where(function ($query) {
                        foreach ($this->words as $word) {
                            $query->where('name', 'LIKE', '%' . Helpers::escapeLike($word) . '%');
                        }
                    });
                    $query->orWhere(function ($query) {
                        foreach ($this->words as $word) {
                            $query->where('text', 'LIKE', '%' . Helpers::escapeLike($word) . '%');
                        }
                    });
                });
            });
    }

    /**
     * @return bool
     */
    public function hasWords(): bool
    {
        return count($this->words) !== 0;
    }

    /**
     * @param $sentence
     * @return array
     */
    private function clearSentence($sentence): array
    {
        return Str::of($sentence)->replace([
            '-',
            '_',
            '/',
            '\\',
            '=',
            '+',
            '%',
            '*',
            '$',
            '@',
            '(',
            ')',
            '[',
            ']',
            '|',
            ',',
            '.',
            ';',
            ':',
            '{',
            '}',
        ], ' ')->explode(' ')->map('trim')->filter()->all();
    }
}
