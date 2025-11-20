<?php

namespace App\Resources\Notification;

use App\Models\Notification\FcmTemplate;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Notification Template Resource",
 *     @OA\Property(property="id", type="string", description="ID", example=1),
 *     @OA\Property(property="active", type="boolean", description="Активен ли шаблон", example=true),
 *     @OA\Property(property="type", type="string", enum={"planned", "postponed"}, description="Тип шаблона", example="planned"),
 *     @OA\Property(property="translations", title="Translations", description="Переводы для шаблогов", type="object",
 *          @OA\Property(property="ru", description="Локаль", type="object",
 *              @OA\Property(property="title", type="string", description="Заголовок шаблона", example="Запланированная демонстрация"),
 *              @OA\Property(property="text", type="string", description="Текст шаблога", example="Добрый день, {Dealer} запланировал демонстрацию"),
 *          ),
 *          @OA\Property(property="en", description="Локаль", type="object",
 *              @OA\Property(property="title", type="string", description="Заголовок шаблона", example="Planned demo"),
 *              @OA\Property(property="text", type="string", description="Текст шаблога", example="Good day, {Dealer} will planned"),
 *          ),
 *     ),
 *     @OA\Property(property="vars", type="array", description="Используемые переменые в тексте",
 *         @OA\Items(type="string", default="dealer")
 *     ),
 * )
 */
class NotificationTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var FcmTemplate $model */
        $model = $this;

        return [
            'id' => $model->id,
            'active' => $model->active,
            'type' => $model->type,
            'vars' => $model->vars,
            'translations' => $this->byTranslationsField($this->translations)
        ];
    }

    private function byTranslationsField($translations)
    {
        if($translations){
            $data = [];
            foreach($translations ?? [] as $translation){
                $data[$translation->lang]['title'] = $translation->title;
                $data[$translation->lang]['text'] = $translation->text;
            }

            return !empty($data) ? $data : null;
        }

        return null;
    }
}
