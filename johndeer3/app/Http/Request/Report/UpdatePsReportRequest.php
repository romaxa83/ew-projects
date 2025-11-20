<?php

namespace App\Http\Request\Report;

use App\Rules\ModelDescriptionRule;
use App\Type\ClientType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Update Ps Report Request",
 *      @OA\Property(property="status", type="integer", example=1,
 *         description="Статус (1-создана, 2-открыта для редактирования ps'у, 3-отредактирована ps'ом, 4-отчет в процессе смздания, 5-отчет верефицирован)"
 *     ),
 *     @OA\Property(property="salesman_name", type="string", description="Имя продовца", example="Буряк Игорь"),
 *     @OA\Property(property="assignment", type="string", description="Назначения", example="демонстрация качества среза и обработки силоса"),
 *     @OA\Property(property="result", type="string", description="Результат", example="великолепно"),
 *     @OA\Property(property="client_comment", type="string", description="Комментарии клиента", example="Техніка сподобалась"),
 *     @OA\Property(property="client_email", type="string", description="Email клиента", example="gahovv@rdo.ua"),
 *     @OA\Property(property="planned_at", type="integer", description="Планируемая дата демонстрации (timestamp с мс.)", example=1651906800000),
 *     @OA\Property(property="clients", type="array",
 *         @OA\Items(
 *              @OA\Property(property="client_id", type="integer", description="ID клиента", example="1"),
 *              @OA\Property(property="type", type="integer", description="Тип клиента (0/1 - конкурент/потенциальный)", example=1),
 *              @OA\Property(property="model_description_id", description="ID model description", type="integer", example=234),
 *              @OA\Property(property="quantity_machine", type="integer", description="Кол-во техники у клиента на руках", example=3),
 *              @OA\Property(property="company_name", type="string", description="Название компание (обязательно если не указано client_id)", example="СГ ТОВ 'МАКАРІВСЬКЕ'"),
 *              @OA\Property(property="customer_last_name", type="string", description="Имя клиента (обязательно если не указано client_id)", example="Юрій"),
 *              @OA\Property(property="customer_second_name", type="string", description="Фамилия клиента (обязательно если не указано client_id)", example="Салата"),
 *              @OA\Property(property="customer_phone", type="string", description="Телефоны клиента (обязательно если не указано client_id)", example="+380449132251"),
 *              @OA\Property(property="customer_id", type="string", description="ID клиента (обязательно если не указано client_id)", example="851254"),
 *              @OA\Property(property="comment", type="string", description="Комментарии к клиенту (обязательно если не указано client_id)", example="хороший клиент"),
 *         )
 *     ),
 *     @OA\Property(property="machines", type="array",
 *         @OA\Items(
 *              @OA\Property(property="manufacturer_id", type="integer", description="ID производителя", example=1),
 *              @OA\Property(property="equipment_group_id", type="integer", description="ID equipment group", example=6),
 *              @OA\Property(property="model_description_id", type="integer", description="ID model description", example=345),
 *              @OA\Property(property="header_brand_id", type="integer", description="ID производитель жатки, выводим список из manufacturer (обязательно для tractor/combine)", example=3),
 *              @OA\Property(property="header_model_id", type="integer", description="ID модели жатки, выводим список из model description (обязательно для tractor/combine)", example=34),
 *              @OA\Property(property="serial_number_header", type="string", description="Серийный номер жатки (обязательно для tractor/combine)", example="2487345HRS"),
 *              @OA\Property(property="trailed_equipment_type", type="string", description="Тип прицепного оборудования (обязательно для tractor)", example="культиватор"),
 *              @OA\Property(property="trailer_model", type="string", description="Модель прицепной техники (обязательно для tractor)", example="2633VT"),
 *              @OA\Property(property="machine_serial_number", type="string", description="Серийный номер машины", example="2487345HRS"),
 *              @OA\Property(property="sub_machine_serial_number", type="string", description="Серийный номер связаной машины (к примеру прицеп для трактора)", example="ЕЕ87345HRS"),
 *              @OA\Property(property="sub_manufacturer_id", type="string", description="Производитель связаной машины (к примеру прицеп для трактора)", example=1),
 *              @OA\Property(property="sub_equipment_group_id", type="string", description="Equipment group связаной машины (к примеру прицеп для трактора)", example=22),
 *              @OA\Property(property="sub_model_description_id", type="string", description="Model description связаной машины (к примеру прицеп для трактора)", example=12),
 *         )
 *     ),
 *     @OA\Property(property="location", type="object",
 *         @OA\Property(property="location_lat", type="string", description="широта", example="46.6372162"),
 *         @OA\Property(property="location_long", type="string", description="долгота", example="32.6121012"),
 *         @OA\Property(property="location_country", type="string", description="страна", example="Украина"),
 *         @OA\Property(property="location_city", type="string", description="город (не обязательно)", example="Херсон"),
 *         @OA\Property(property="location_region", type="string", description="область", example="Херсонська область"),
 *         @OA\Property(property="location_street", type="string", description="улица (не обязательно)", example="вулиця Ярослава Мудрого16"),
 *         @OA\Property(property="location_zipcode", type="string", description="почтовый индекс", example="73000"),
 *         @OA\Property(property="location_district", type="string", description="район", example="Корабельный район"),
 *     ),
 *     @OA\Property(property="features", type="array", description="Секция для заполнения характеристик техники ,при демонстрации, набор характеристик подтягиваются при выборе Equipment group (у каждой свой набор, или может небыть вовсе)",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer", example="id", description="ID характеристики"),
 *             @OA\Property(property="is_sub", type="boolean", example="true",
 *                 description="Относится ли характеристика к доп. техники, при демонстрации"
 *             ),
 *             @OA\Property(property="group", type="array",
 *                 description="секция для заполнения секций характеристик (может быть несколько, для кажждой Model Description), можно указывать или кастомной значение или выбирать из списка, если у данной характеристик предустановленны значения",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=22,
 *                         description="ID Model Description к которой относится данное значение характеристики"
 *                     ),
 *                     @OA\Property(property="name", type="string", example="Pelican 1200",
 *                         description="Название Model Description к которой относится данное значение характеристики"
 *                     ),
 *                     @OA\Property(property="value", type="string", example="1200",
 *                         description="Вносимое значение ps`ом для данной характеристик и model description(если их несколько)"
 *                     ),
 *                     @OA\Property(property="choiceId", type="integer", example="12",
 *                         description="ID значения характеристики, если данная характеристика имеет несколько значений, которые созданы и присвоены ей заранее"
 *                     ),
 *                  )
 *              ),
 *         )
 *     ),
 *     @OA\Property(property="files", type="object", description="
 *          - Если картинки не нужно менять не шлем массив files либо шлем пустой массив (сохраненые до этого файлы не будут затронуты), или шлем по сохраненым файлам их basename (которое возвращаеться с файлом),файлы по которым не пришел basename, будут удалены
 *          - Если нужно добавить новые фото, но не затронуть старые, нужно присылать basename для все файлов и слать новые файлы,файлы по которым не было прислано basename будут удалены
 *          'files' => [
 *              'other' => [
 *                  0 => 'basename.png'
 *                  1 => 'basename.png'
 *                  2 => Illuminate\Http\UploadedFile
 *              ],
 *          ]
 *          - Если нужно удалить все фото из модуля , нужно прислать модуль с пустым массивом, и тогда в данном модуле будут удалены все файлы
 *          'files' => [
 *              'other' => [],
 *          ]
 *     ",
 *         @OA\Property(property="working_hours_at_the_beg", type="array",
 *             description="Рабочее время в начале",
 *             maximum=2,
 *             example={"image_1","image_2"},
 *             @OA\Items(
 *                 @OA\Property(property="image_1", type="file"),
 *                 @OA\Property(property="image_2", type="file"),
 *             ),
 *         ),
 *         @OA\Property(property="working_hours_at_the_end", type="array",
 *             description="Рабочее время в конце",
 *             maximum=2,
 *             example={"image_1","image_2"},
 *             @OA\Items(
 *                 @OA\Property(property="image_1", type="file"),
 *                 @OA\Property(property="image_2", type="file"),
 *             ),
 *         ),
 *         @OA\Property(property="equipment_on_the_field", type="array",
 *             description="Техника на поле",
 *             maximum=5,
 *             example={"image_1","image_2"},
 *             @OA\Items(
 *                 @OA\Property(property="image_1", type="file"),
 *                 @OA\Property(property="image_2", type="file"),
 *             ),
 *         ),
 *         @OA\Property(property="me_and_equipment", type="array",
 *             description="Техника на я",
 *             maximum=5,
 *             example={"image_1","image_2"},
 *             @OA\Items(
 *                 @OA\Property(property="image_1", type="file"),
 *                 @OA\Property(property="image_2", type="file"),
 *             ),
 *         ),
 *         @OA\Property(property="others", type="array",
 *             description="Другое",
 *             maximum=5,
 *             example={"image_1","image_2"},
 *             @OA\Items(
 *                 @OA\Property(property="image_1", type="file"),
 *                 @OA\Property(property="image_2", type="file"),
 *             ),
 *         ),
 *         @OA\Property(property="signature", type="array",
 *             description="Подпись",
 *             maximum=1,
 *             example={"[23,345,6,3,2,435,56]"},
 *             @OA\Items(@OA\Property(property="signature", type="string", description="Строка байтов", example="[23,345,6,3,2,435,56]")),
 *         ),
 *     ),
 *     required={"status"}
 * )
 */

class UpdatePsReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'salesman_name' => ['nullable', 'string'],
            'machine_for_compare' => ['nullable', 'string'],
            'planned_at' => ['nullable'],

            'clients' => ['nullable', 'array'],
            'clients.*.client_id' => ['nullable', 'integer', 'exists:jd_clients,id'],
            'clients.*.type' => ['nullable','integer', 'in:'.ClientType::TYPE_COMPETITOR.','.ClientType::TYPE_POTENTIAL],
            'clients.*.company_name' => ['nullable', 'string'],
            'clients.*.customer_last_name' => ['nullable', 'string'],
            'clients.*.customer_first_name' => ['nullable', 'string'],
            'clients.*.customer_phone' => ['nullable','max:191'],
            'clients.*.customer_id' => ['nullable', 'string'],
            'clients.*.comment' => ['nullable', 'string'],
            'clients.*.quantity_machine' => ['nullable', 'integer'],
            'clients.*.model_description_id' => ['nullable', 'integer'],

            'machines' => ['nullable', 'array'],
            'machines.*.manufacturer_id' => ['nullable', 'integer'],
            'machines.*.equipment_group_id' => ['nullable', 'integer', 'exists:jd_equipment_groups,id'],
            'machines.*.model_description_id' => ['nullable', 'integer', 'exists:jd_model_descriptions,id', new ModelDescriptionRule()],
            'machines.*.header_brand_id' => ['nullable', 'integer', 'exists:jd_manufacturers,id'],
            'machines.*.header_model_id' => ['nullable', 'integer', 'exists:jd_model_descriptions,id'],
            'machines.*.serial_number_header' => ['nullable', 'string', 'max:100'],
            'machines.*.machine_serial_number' => ['nullable', 'string', 'max:100'],
            'machines.*.trailed_equipment_type' => ['nullable', 'string', 'max:100'],
            'machines.*.trailer_model' => ['nullable', 'string', 'max:200'],
            'machines.*.sub_manufacturer_id' => ['nullable', 'integer'],
            'machines.*.sub_equipment_group_id' => ['nullable', 'integer', 'exists:jd_equipment_groups,id'],
            'machines.*.sub_model_description_id' => ['nullable', 'integer', 'exists:jd_model_descriptions,id'],
            'machines.*.sub_machine_serial_number' => ['nullable', 'string', 'max:100'],

            'location' => ['nullable', 'array'],
            'location.location_lat' => ['nullable', 'string', 'max:50'],
            'location.location_long' => ['nullable', 'string', 'max:50'],
            'location.location_country' => ['nullable', 'string', 'max:50'],
            'location.location_city' => ['nullable', 'string', 'max:50'],
            'location.location_region' => ['nullable', 'string', 'max:100'],
            'location.location_zipcode' => ['nullable', 'string', 'max:50'],
            'location.location_street' => ['nullable', 'string', 'max:150'],
            'location.location_district' => ['nullable', 'string'],

            'features' => ['nullable', 'array'],
            'features.*.id' => ['required'],
//            'features.*.value' => ['nullable', 'string'],
            'features.*.is_sub' => ['nullable'],
            'features.*.group.*.id' => ['nullable', 'integer', 'exists:jd_model_descriptions,id'],
            'features.*.group.*.name' => ['nullable', 'string', 'exists:jd_model_descriptions,name'],
            'features.*.group.*.value' => ['nullable', 'string'],
            'features.*.group.*.choiceId' => ['nullable', 'integer', 'exists:feature_values,id'],

            'assignment' => ['nullable', 'string'],
            'result' => ['nullable', 'string'],
            'client_comment' => ['nullable', 'string'],
            'client_email' => ['nullable']
        ];
    }
}
