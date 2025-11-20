<?php

namespace App\Http\Request\Admin\Report;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Update Report Request",
 *     @OA\Property(property="salesman_name", type="string", description="Имя продовца", example="Буряк Игорь"),
 *     @OA\Property(property="title", type="string", description="Заголовок отчета", example="Agrotek_ТОВ_Дружба-5_8345r_04-07-2020"),
 *     @OA\Property(property="assignment", type="string", description="Назначения", example="демонстрация качества среза и обработки силоса"),
 *     @OA\Property(property="result", type="string", description="Результат", example="великолепно"),
 *     @OA\Property(property="client_comment", type="string", description="Комментарии клиента", example="Техніка сподобалась"),
 *     @OA\Property(property="comment", type="string", description="Комментарии админа", example="Не указана модель техники"),
 *     @OA\Property(property="machine", type="array", description="Machine data",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer", description="ID machine report", example=22),
 *             @OA\Property(property="equipment_group_id", type="integer", description="ID equipment group", example=15),
 *             @OA\Property(property="model_description_id", type="integer", description="ID model description", example=344),
 *             @OA\Property(property="header_brand_id", type="integer", description="Производитель жатки (обязательно для tractor/combine)", example=22),
 *             @OA\Property(property="header_model_id", type="integer", description="Модель жатки (обязательно для tractor/combine)", example=345),
 *             @OA\Property(property="serial_number_header", type="string", description="Серийный номер жатки (обязательно для tractor/combine)", example="2487345HRS"),
 *             @OA\Property(property="trailed_equipment_type", type="string", description="Тип прицепного оборудования (обязательно для tractor)", example="культиватор"),
 *             @OA\Property(property="trailer_model", type="string", description="Модель прицепного оборудования (обязательно для tractor)", example="2633VT"),
 *             @OA\Property(property="machine_serial_number", type="string", description="Серийный номер машины", example="2487345HRS"),
 *         )
 *     ),
 * )
 */

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string'],
            'salesman_name' => ['nullable', 'string'],
            'assignment' => ['nullable', 'string'],
            'result' => ['nullable', 'string'],
            'client_comment' => ['nullable', 'string'],
            'client_email' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],

            'machines' => ['nullable', 'array'],
            'machines.*.id' => ['required', 'integer', 'exists:reports_machines,id'],
            'machines.*.equipment_group_id' => ['nullable', 'integer', 'exists:jd_equipment_groups,id'],
            'machines.*.model_description_id' => ['nullable', 'integer', 'exists:jd_model_descriptions,id'],
            'machines.*.header_brand_id' => ['nullable', 'integer', 'exists:jd_manufacturers,id'],
            'machines.*.header_model_id' => ['nullable', 'integer', 'exists:jd_model_descriptions,id'],
            'machines.*.serial_number_header' => ['nullable', 'string', 'max:200'],
            'machines.*.machine_serial_number' => ['nullable', 'string', 'max:200'],
            'machines.*.trailed_equipment_type' => ['nullable', 'string', 'max:200'],
            'machines.*.trailer_model' => ['nullable', 'string', 'max:200'],
        ];
    }
}
