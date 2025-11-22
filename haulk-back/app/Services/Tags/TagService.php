<?php

namespace App\Services\Tags;

use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Tags\Tag;
use App\Exceptions\Tag\MaxCountReachedException;
use DB;
use Exception;
use Log;

class TagService
{
    public function create(array $attributes): Tag
    {
        if (Tag::query()->where('type', $attributes['type'])->count() >= Tag::MAX_TAGS_COUNT_PER_TYPE) {
            throw new MaxCountReachedException();
        }

        try {
            DB::beginTransaction();

            $tag = Tag::query()->make($attributes);

            $tag->saveOrFail();

            DB::commit();

            return $tag;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Tag $tag, array $attributes): Tag
    {
        try {
            DB::beginTransaction();

            $tag->update($attributes);

            DB::commit();

            return $tag;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Tag $tag): Tag
    {
        if ($tag->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        $tag->delete();

        return $tag;
    }

    public function getList(array $attributes, array $types): array
    {
        $tags = Tag::query()
            ->filter($attributes)
            ->orderBy('name', 'desc')
            ->get();

        $list = [];
        foreach ($types as $type) {
            $list[$type] = [];
        }

        /** @var Tag $item */
        foreach ($tags as $item) {
            if (!isset($list[$item->type])) {
                $list[$item->type] = [];
            }
            $itemData = [
                'id' => $item->id,
                'name' => $item->name,
                'color' => $item->color,
                'type' => $item->type,
                'hasRelatedEntities' => $item->hasRelatedEntities(),
            ];

            if ($item->type === Tag::TYPE_TRUCKS_AND_TRAILER) {
                $itemData['hasRelatedTrucks'] = $item->trucks()->exists();
                $itemData['hasRelatedTrailers'] = $item->trailers()->exists();
            }
            $list[$item->type][] = $itemData;
        }

        return $list;
    }
}
