<?php

namespace App\Repositories\Tags;

use App\Enums\Tags\TagType;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Tags\Tag;
use Illuminate\Database\Eloquent\Model;

final readonly class TagRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Tag::class;
    }

    public function list(array $filters = []): array
    {
        $tags = Tag::query()
            ->filter($filters)
            ->orderBy('name', 'desc')
            ->get();

        $list = [];
        foreach (TagType::asArray() as $type) {
            $list[$type] = [];
        }

        /** @var Tag $item */
        foreach ($tags as $item) {
            if (!isset($list[$item->type->value])) {
                $list[$item->type->value] = [];
            }
            $itemData = [
                'id' => $item->id,
                'name' => $item->name,
                'color' => $item->color,
                'type' => $item->type,
                'hasRelatedEntities' => $item->hasRelatedEntities(),
            ];

            if ($item->type->isTrucksAndTrailer()) {
                $itemData['hasRelatedTrucks'] = $item->trucks()->exists();
                $itemData['hasRelatedTrailers'] = $item->trailers()->exists();
            }
            $list[$item->type->value][] = $itemData;
        }

        return $list;
    }

    public function getEcommTag(): Model|Tag|null
    {
        return Tag::query()
            ->where('type', TagType::CUSTOMER)
            ->where('name', Tag::ECOM_NAME_TAG)
            ->first();
    }
}
