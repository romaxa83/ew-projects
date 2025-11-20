<?php

namespace App\Http\Request\User;

use App\Models\User\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="Create User Request",
 *     @OA\Property(property="login", type="string", description="Логин", example="cubic"),
 *     @OA\Property(property="email", type="string", description="Email", example="cubic@rubic.com"),
 *     @OA\Property(property="phone", type="string", description="Телефон", example="+380990000001"),
 *     @OA\Property(property="country_id", type="integer", description="ID национальности", example=1),
 *     @OA\Property(property="first_name", type="string", description="Имя", example="Cubic"),
 *     @OA\Property(property="last_name", type="string", description="Фамилия", example="Rubic"),
 *     @OA\Property(property="role", type="string", description="Роль", example="ps"),
 *     @OA\Property(property="dealer_id", type="integer", description="ID дилера (обязательно если выбрана роль ps)", example=1),
 *     @OA\Property(property="dealer_ids", description="Массив ID дилеров (обязательно если выбрана роль tm/tmd)", example="[1, 4]"),
 *     @OA\Property(property="eg_ids", description="Массив ID equipment group, (обязательно если выбрана роль pss)", example="[5, 44]"),
 *     required={"login", "email", "phone", "country_id", "first_name", "last_name", "role"}
 * )
 */

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'regex:/^[A-z0-9 -]*$/u', 'max:191', Rule::unique('users')],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('users')],
            'phone' => ['required', 'string', 'regex:/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/','max:191',  Rule::unique('users')],
            'country_id' => ['required',  'integer', 'exists:nationalities,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:'.Role::ROLE_PS.','.Role::ROLE_PSS.','.Role::ROLE_TMD],
            'dealer_id' => ['required_if:role,'.Role::ROLE_PS, 'integer', 'exists:jd_dealers,id'],
            'eg_ids' => ['required_if:role,' . Role::ROLE_PSS, 'array'],
            'eg_ids.*' => ['exists:jd_equipment_groups,id'],
            'dealer_ids' => ['required_if:role,' . Role::ROLE_TMD, 'array'],
            'dealer_ids.*' => ['exists:jd_dealers,id'],
        ];
    }
}
