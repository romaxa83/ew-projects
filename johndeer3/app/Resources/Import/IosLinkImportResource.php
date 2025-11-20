<?php

namespace App\Resources\Import;

use App\Helpers\DateFormat;
use App\Models\Import\IosLinkImport;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="IosLink Import Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="status", type="string", description="Статус импорта",
 *         example="done", enum={"new", "in_process", "failed", "done"}),
 *     @OA\Property(property="user_id", type="integer", description="ID пользователя, которому присмвоен данный линк", example=1),
 *     @OA\Property(property="user_name", type="string", description="Имя пользователя", example="Cubic Rubic"),
 *     @OA\Property(property="message", type="string", description="Сообщение", example="Created count ios-links - 299"),
 *     @OA\Property(property="file", type="string", description="Ссылка на загруженый файл", example="http://123.434.5.6/storage/ios-links-import/1637495136.xls"),
 *     @OA\Property(property="created_at", type="string", description="Создание", example="21.11.2021 13:45"),
 *     @OA\Property(property="updated_at", type="string", description="Обновление", example="21.11.2021 13:45"),
 *     @OA\Property(property="error_data", title="Массив ошибок", type="array",  @OA\Items(type="array", @OA\Items()))
 * )
 */
class IosLinkImportResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var IosLinkImport $iosLinkImport */
        $iosLinkImport = $this;
        return [
            'id' => $iosLinkImport->id,
            'user_id' => $iosLinkImport->user_id,
            'user_name' => $iosLinkImport->user ? $iosLinkImport->user->full_name : null,
            'status' => $iosLinkImport->status,
            'file' => $iosLinkImport->file_link,
            'message' => $iosLinkImport->message,
            'created_at' => DateFormat::front($iosLinkImport->created_at),
            'updated_at' => DateFormat::front($iosLinkImport->updated_at),
            'error_data' => ($iosLinkImport->error_data) ?? [],
        ];
    }

    /**
     * @SWG\Definition(definition="IosLinkImport",
     *      @SWG\Property(property="id", type="integer", example = 1),
     *      @SWG\Property(property="status", type="string"),
     *      @SWG\Property(property="user_id", type="string"),
     *      @SWG\Property(property="user_name", type="string"),
     *      @SWG\Property(property="message", type="string"),
     *      @SWG\Property(property="file", type="string"),
     *      @SWG\Property(property="created_at", type="string"),
     *      @SWG\Property(property="updated_at", type="string"),
     *      @SWG\Property(property="error_data", type="array", collectionFormat="multi",
     *          @SWG\Items(type="array",collectionFormat="multi",
     *          @SWG\Items(type="string")
     *      )
     *   ),
     * )
     */
}
