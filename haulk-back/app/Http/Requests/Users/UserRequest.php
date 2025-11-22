<?php

namespace App\Http\Requests\Users;

use App\Dto\UserDto;
use App\Models\Tags\Tag;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Route;
use Spatie\Permission\Models\Role;

/**
 * @property string email
 * @property string role
 * @property int role_id
 */
class UserRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        if (Route::getCurrentRoute()->parameter('user')) {
            $this->user()->can('users update');
        }

        return $this->user()->can('users create');
    }

    public function rules(): array
    {
        $user = Route::getCurrentRoute()->parameter('user');

        $rules = [
            'full_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'phone' => ['nullable', 'string', $this->USAPhone(), 'max:191'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', $this->USAPhone(), 'string', 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            User::ATTACHMENT_FIELD_NAME => ['nullable', 'array'],
            User::ATTACHMENT_FIELD_NAME . '.*' => ['file', $this->orderAttachmentTypes()],
            'first_name' => ['nullable'],
            'last_name' => ['nullable'],
        ];

        if ($user) {
            $rules['email'] = ['required', 'email', $this->email(), 'unique:users,email,' . $user->id, 'max:191'];
        } else {
            $rules['email'] = ['required', 'email', $this->email(), 'unique:users,email', 'max:191'];
        }

        $assignedRoleName = $this->getAssignedRole()->name ?? null;
        if ($assignedRoleName) {
            $rules['user'][] = static function ($attribute, User $value, $fail) use ($assignedRoleName) {
                if (
                    $value->isDriver()
                    && !in_array($assignedRoleName, User::DRIVER_ROLES, true)
                    && ($value->truck || $value->trailer)
                ) {
                    $fail(trans('Driver has vehicles assigned. Please reassign the vehicles first.'));
                    return;
                }

                if (
                    $value->isOwner()
                    && !in_array($assignedRoleName, User::OWNER_ROLES, true)
                    && ($value->ownerTrucks()->exists() || $value->ownerTrailers()->exists())
                ) {
                    $fail(trans('Owner has vehicles assigned. Please reassign the vehicles first.'));
                    return;
                }
            };
        }

        return $this->addAttributesForAssignedRole($rules);
    }

    //В будущем нужно заменить на фильтрацию по пермишенам
    protected function addAttributesForAssignedRole(array $rules): array
    {
        if (!($role = $this->getAssignedRole())) {
            return $rules;
        }

        switch ($role->getAttribute('name')) {
            case User::SUPERADMIN_ROLE:
                return $rules + $this->additionalSuperAdminRules();

            case User::ADMIN_ROLE:
                return $rules + $this->additionalAdminRules();

            case User::DRIVER_ROLE:
                return $rules + $this->additionalDriverRules();

            case User::OWNER_DRIVER_ROLE:
                return $rules + $this->additionalDriverRules() + $this->additionalOwnerRules();

            case User::OWNER_ROLE:
                return $rules + $this->additionalOwnerRules();

            case User::DISPATCHER_ROLE:
                return $rules + $this->additionalDispatcherRules();
        }

        return $rules;
    }

    public function getAssignedRole(): ?Role
    {
        if (!$this->role_id) {
            return null;
        }

        return resolve(RoleRepository::class)->findById($this->role_id);
    }

    protected function additionalSuperAdminRules(): array
    {
        return [
            'can_check_orders' => ['nullable', 'boolean'],
        ];
    }

    protected function additionalAdminRules(): array
    {
        return [
            'can_check_orders' => ['nullable', 'boolean'],
        ];
    }

    protected function additionalDriverRules(): array
    {
        return [
            'owner_id' => ['required', 'integer', 'exists:users,id'],
            'driver_rate' => ['nullable', 'integer', 'max:100'],
            'notes' => ['nullable', 'string'],
            'driver_license' => ['nullable', 'array'],
            'driver_license.license_number' => ['nullable', 'string', 'max:16'],
            'driver_license.issuing_state_id' => ['nullable', 'integer', 'exists:states,id'],
            'driver_license.issuing_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'driver_license.expiration_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'driver_license.category' => ['nullable', 'string', Rule::in(array_keys(DriverLicense::CATEGORIES))],
            'driver_license.category_name' => ['nullable', 'required_if:driver_license.category,other', 'string', 'max:10'],
            'driver_license.' . DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'previous_driver_license' => ['nullable', 'array'],
            'previous_driver_license.license_number' => ['nullable', 'string', 'max:16'],
            'previous_driver_license.is_usa' => ['nullable', 'bool'],
            'previous_driver_license.issuing_country' => ['nullable', 'string'],
            'previous_driver_license.issuing_state_id' => ['nullable', 'required_if:previous_driver_license.is_usa,true', 'integer', 'exists:states,id'],
            'previous_driver_license.issuing_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'previous_driver_license.expiration_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'previous_driver_license.category' => ['nullable', 'string', Rule::in(array_keys(DriverLicense::CATEGORIES))],
            'previous_driver_license.category_name' => ['nullable', 'required_if:previous_driver_license.category,other', 'string', 'max:10'],
            'previous_driver_license.' . DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'medical_card' => ['nullable', 'array'],
            'medical_card.card_number' => ['nullable', 'string', 'max:16'],
            'medical_card.issuing_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'medical_card.expiration_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'medical_card.' . DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME => [['file', 'mimes:pdf,png,jpg,jpeg', 'max:10240']],
            'mvr' => ['nullable', 'array'],
            'mvr.reported_date' => ['nullable', 'string', 'date_format:m/d/Y'],
            'mvr.' . DriverInfo::ATTACHED_MVR_FILED_NAME => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'has_company' => ['nullable', 'bool'],
            'company_info' =>  ['nullable', 'required_if:has_company,true', 'array'],
            'company_info.name' => ['required_if:has_company,true', 'string', 'max:20'],
            'company_info.ein' => ['required_if:has_company,true', 'alpha_dash', 'max:20'],
            'company_info.address' => ['nullable', 'string', 'max:150'],
            'company_info.city' => ['required_if:has_company,true', 'string'],
            'company_info.zip' => ['required_if:has_company,true', 'string'],
        ];
    }

    protected function additionalDispatcherRules(): array
    {
        return [
            'can_check_orders' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'owner_id' => trans('Assign to dispatcher'),
        ];
    }

    protected function prepareForValidation()
    {
        $this->transformPhoneAttribute('phone');

        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }

        $user = $this->route()->parameter('user');

        if ($user) {
            $this->merge([
                'user' => $user,
            ]);
        }

        $this->splitName();

        if(!$this->has('has_company')) {
            $this->has_company = false;
        }
    }

    protected function splitName():void
    {
        if ($this->has('full_name') && !empty($this->input('full_name'))) {
            $data = explode(' ', $this->input('full_name'), 2);
            $this->merge(
                [
                    'first_name' => $data[0] ?? '',
                    'last_name' => $data[1] ?? '',
                ]
            );
        }
    }

    protected function additionalOwnerRules(): array
    {
        return [
            'tags' => ['nullable' ,'array', 'max:5'],
            'tags.*' => [
                'required',
                'int',
                Rule::exists(Tag::TABLE_NAME, 'id')
                    ->where('type', Tag::TYPE_VEHICLE_OWNER)
            ],
        ];
    }

    public function getDto(): UserDto
    {
        return UserDto::byParams($this->validated());
    }
}

/**
 * @OA\Schema(schema="UserRequest", type="object", allOf={
 *      @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email","status","security_level"},
 *          @OA\Property(property="first_name", type="string", description="User first name", nullable=false),
 *          @OA\Property(property="last_name", type="string", description="User last name", nullable=false),
 *          @OA\Property(property="email", type="string", description="User email", nullable=false),
 *          @OA\Property(property="phone", type="string", description="User phone", nullable=true),
 *          @OA\Property(property="role_id", type="integer", description="User role id", nullable=false),
 *          @OA\Property(property="phone_extension", type="string", description="User phone extension", nullable=true),
 *          @OA\Property(property="phones", type="array", description="User aditional phones", nullable=true,
 *              @OA\Items(ref="#/components/schemas/PhonesRaw")
 *          ),
 *          @OA\Property(property="owner_id", type="integer", description="Owner for user(if user is driver)", nullable=true),
 *          @OA\Property(property="driver_rate", type="integer", description="Driver rate (if user is driver)", nullable=true),
 *          @OA\Property(property="notes", type="string", description="Driver notes (if user is driver)", nullable=true),
 *          @OA\Property(property="attachment_files", type="array", nullable=true,
 *               items=@OA\Items(type="string", format="binary",),
 *          ),
 *          @OA\Property(property="can_check_orders", type="boolean", description="Dispatcher can chack orders", nullable=true),
 *          @OA\Property(property="tags", type="array", items=@OA\Items(type="integer"), nullable=true),
 *          @OA\Property(property="medical_card", type="object", description="Driver medical card", nullable=true,
 *              allOf={
 *                  @OA\Schema(
 *                      required={},
 *                      @OA\Property(property="card_number", type="string", description="Medical card number", nullable=true),
 *                      @OA\Property(property="issuing_date", type="string", description="Medical card issuing date, format m/d/Y", nullable=true),
 *                      @OA\Property(property="expiration_date", type="string", description="Medical card expiration date, format m/d/Y", nullable=true),
 *                      @OA\Property(property="medical_card_document", type="string", format="binary", description="Medical card document", nullable=true,),
 *                  )
 *              }
 *          ),
 *          @OA\Property(property="mvr", type="object", description="Driver mvr", nullable=true,
 *              allOf={
 *                  @OA\Schema(
 *                      required={},
 *                      @OA\Property(property="reported_date", type="string", description="MVR reported date, , format m/d/Y", nullable=true),
 *                      @OA\Property(property="mvr_document", type="string",  format="binary", description="Medical card document", nullable=true),
 *                  )
 *              }
 *          ),
 *          @OA\Property(property="driver_license", type="object", description="Driver license", nullable=true,
 *              allOf={
 *                  @OA\Schema(
 *                      required={},
 *                      @OA\Property(property="license_number", type="string", description="Driver license card number", nullable=true),
 *                      @OA\Property(property="issuing_date", type="string", description="Driver license issuing date, , format m/d/Y", nullable=true),
 *                      @OA\Property(property="expiration_date", type="string", description="Driver license expiration date, , format m/d/Y", nullable=true),
 *                      @OA\Property(property="issuing_state_id", type="integer", description="Driver license issuing state id", nullable=true),
 *                      @OA\Property(property="category", type="string", description="Driver license category", nullable=true),
 *                      @OA\Property(property="category_name", type="string", description="Driver license category name (for Other category)", nullable=true),
 *                      @OA\Property(property="attached_document", type="string", format="binary", description="Driver license document", nullable=true),
 *                  )
 *              }
 *          ),
 *          @OA\Property(property="previous_driver_license", type="object", description="Previous Driver license", nullable=true,
 *              allOf={
 *                  @OA\Schema(
 *                      required={},
 *                      @OA\Property(property="license_number", type="string", description="Driver license card number", nullable=true),
 *                      @OA\Property(property="issuing_date", type="string", description="Driver license issuing date, , format m/d/Y", nullable=true),
 *                      @OA\Property(property="expiration_date", type="string", description="Driver license expiration date, , format m/d/Y", nullable=true),
 *                      @OA\Property(property="is_usa", type="boolean", description="Is Driver license of USA", nullable=true),
 *                      @OA\Property(property="issuing_country", type="string", description="Driver license country", nullable=true),
 *                      @OA\Property(property="issuing_state_id", type="integer", description="Driver license issuing state id", nullable=true),
 *                      @OA\Property(property="category", type="string", description="Driver license category", nullable=true),
 *                      @OA\Property(property="category_name", type="string", description="Driver license category name (for Other category)", nullable=true),
 *                      @OA\Property(property="attached_document", type="string", format="binary", description="Driver license document", nullable=true),
 *                  )
 *              }
 *          ),
 *          @OA\Property(property="has_company", type="boolean", description="Is Driver has company", nullable=false),
 *          @OA\Property(property="company_info", type="object", description="Driver company info", nullable=true,
 *              allOf={
 *                  @OA\Schema(
 *                      required={},
 *                      @OA\Property(property="name", type="string", description="Driver company name", nullable=false),
 *                      @OA\Property(property="ein", type="string", description="Driver company ein", nullable=false),
 *                      @OA\Property(property="address", type="string", description="Driver company address", nullable=true),
 *                      @OA\Property(property="city", type="string", description="Driver company city", nullable=false),
 *                      @OA\Property(property="zip", type="string", description="Driver license country", nullable=false),
 *                  )
 *              }
 *          ),
 *      )
 * })
 */
