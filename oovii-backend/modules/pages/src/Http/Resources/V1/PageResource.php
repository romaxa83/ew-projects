<?php

namespace WezomCms\Pages\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Page Resource",
 *     description="Page Resource",
 * )
 */
class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->translation->name,
            'text' => $this->translation->text,
            'locale' => $this->translation->locale,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID", example=1),
     * @OA\Property(property="type", title="Type",
     *      description="Тип страницы для их идентификации, допустимые значения - agreement/rules/private-policy",
     *      example="agreement"
     * ),
     * @OA\Property(property="title", title="Title", description="Title", example="Some title"),
     * @OA\Property(property="text", title="Text", description="Text", example="<p>Some text</p>"),
     * @OA\Property(property="locale", title="Locale",
     *     description="Локаль переданного языка, допустимые занчения - ru/en/kk",
     *     example="kk"
     * )
     */
}

