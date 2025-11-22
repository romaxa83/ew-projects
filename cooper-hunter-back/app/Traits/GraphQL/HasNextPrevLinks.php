<?php

namespace App\Traits\GraphQL;

use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait HasNextPrevLinks
{
    protected function getNextPrevLinks(): array
    {
        return [
            'prevId' => [
                'type' => Type::id(),
                'selectable' => false,
            ],
            'nextId' => [
                'type' => Type::id(),
                'selectable' => false,
            ],
            'prevSlug' => [
                'type' => Type::string(),
                'selectable' => false,
            ],
            'nextSlug' => [
                'type' => Type::string(),
                'selectable' => false,
            ],
        ];
    }

    protected function setNextPrevLinks(LengthAwarePaginator $paginator, array $args): void
    {
        if ($paginator->total() === 1) {
            $model = $paginator->items()[0];

            unset($args['ids'], $args['slugs']);

            $prevModel = $this->model::query()
                ->filter($args)
                ->where('id', '<', $model->id)
                ->max('id');

            $nextModel = $this->model::query()
                ->filter($args)
                ->where('id', '>', $model->id)
                ->min('id');

            $model->prevId = $prevModel;
            $model->nextId = $nextModel;

            $modelsWithSlugs = $this->model::query()
                ->select(['id', 'slug'])
                ->whereKey([$prevModel, $nextModel])
                ->get();

            $model->prevSlug = $modelsWithSlugs->where('id', $prevModel)->first()?->slug ?? null;
            $model->nextSlug = $modelsWithSlugs->where('id', $nextModel)->first()?->slug ?? null;
        }
    }
}
