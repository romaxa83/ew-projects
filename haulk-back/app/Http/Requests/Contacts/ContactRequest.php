<?php

namespace App\Http\Requests\Contacts;

use App\Dto\Contacts\ContactDto;
use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use App\Models\Contacts\Contact;
use App\Models\Locations\State;
use App\Rules\Contacts\UniqEmailRule;
use App\Rules\Contacts\UniqFullNameRule;
use App\Rules\Contacts\UniqPhoneRule;
use App\Services\TimezoneService;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->transformPhoneAttribute('phone');
    }

    public static function getRules(string $key = '', bool $willBeSaved = true): array
    {
        $key = $key ? $key . '.' : '';
        return [
            $key . 'full_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/[a-z]{2,}/i',
                new UniqFullNameRule($willBeSaved)
            ],
            $key . 'address' => [
                'required',
                'string',
                'min:2',
                'max:255'
            ],
            $key . 'city' => [
                'required',
                'string',
                'max:255'
            ],
            $key . 'state_id' => [
                'required',
                'int',
                Rule::exists(State::class, 'id')
            ],
            $key . 'comment' => [
                'nullable',
                'string'
            ],
            $key . 'zip' => [
                'required',
                'string',
                'max:255'
            ],
            $key . 'phone' => [
                'required',
                self::USAPhone(),
                'max:255',
                new UniqPhoneRule($willBeSaved)
            ],
            $key . 'phone_extension' => [
                'nullable',
                'string',
                'max:255'
            ],
            $key . 'phone_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            $key . 'phones' => [
                'nullable',
                'array',
            ],
            $key . 'phones.*.name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'phones.*.number' => [
                'required_with:' . $key . 'phones.*.extension',
                'nullable',
                self::USAPhone(),
                'max:191',
                new UniqPhoneRule($willBeSaved)
            ],
            $key . 'phones.*.extension' => [
                'nullable',
                'string',
                'max:255'
            ],
            $key . 'phones.*.notes' => [
                'nullable',
                'string',
                'max:255'
            ],
            $key . 'email' => [
                $willBeSaved ? 'required' : 'nullable',
                'string',
                'max:255',
                new UniqEmailRule($willBeSaved)
            ],
            $key . 'fax' => [
                'nullable',
                'string',
                'max:255'
            ],
            $key . 'type_id' => [
                'required',
                'int',
                Rule::in(array_keys(Contact::CONTACT_TYPES))
            ],
            $key . 'timezone' => [
                'required',
                Rule::in(resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->toArray())
            ],
            $key . 'working_hours' => [
                'nullable',
                'array'
            ],
            $key . 'working_hours.*.from' => [
                'required_with:' . $key . 'working_hours',
                'date_format:' . DateTimeHelper::TIME_FORMAT
            ],
            $key . 'working_hours.*.to' => [
                'required_with:' . $key . 'working_hours',
                'date_format:' . DateTimeHelper::TIME_FORMAT
            ],
            $key . 'working_hours.*.dayoff' => [
                'required_with:' . $key . 'working_hours',
                'boolean'
            ],
        ];
    }

    public function rules(): array
    {
        return self::getRules();
    }

    public function toDto(): ContactDto
    {
        return ContactDto::init($this->validated());
    }

    /**
     * @OA\Schema (
     *     schema="ContactRequest",
     *     type="object",
     *     required={
     *          "full_name",
     *          "address",
     *          "city",
     *          "state_id",
     *          "zip",
     *          "type_id",
     *          "timezone",
     *     },
     *     properties={
     *          @OA\Property(
     *               property="full_name",
     *               type="string",
     *               minLength=2,
     *               maxLength=255,
     *               nullable=false,
     *          ),
     *          @OA\Property(
     *               property="address",
     *               type="string",
     *               minLength=2,
     *               maxLength=255,
     *               nullable=false
     *          ),
     *          @OA\Property(
     *               property="city",
     *               type="string",
     *               minLength=2,
     *               maxLength=255,
     *               nullable=false
     *          ),
     *          @OA\Property(
     *               property="state_id",
     *               type="integer",
     *               nullable=false
     *          ),
     *          @OA\Property(
     *               property="comment",
     *               type="string",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="zip",
     *               type="string",
     *               nullable=false
     *          ),
     *          @OA\Property(
     *               property="phone",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property(
     *               property="phone_extension",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property(
     *               property="phone_name",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property(
     *               property="phones",
     *               type="array",
     *               nullable=true,
     *               items= @OA\Items(
     *                  type="object",
     *                  properties={
     *                      @OA\Property (
     *                          property="name",
     *                          type="string",
     *                          nullable=true,
     *                          maxLength=255,
     *                      ),
     *                      @OA\Property (
     *                          property="number",
     *                          type="string",
     *                          nullable=true,
     *                          maxLength=255,
     *                      ),
     *                      @OA\Property (
     *                          property="extension",
     *                          type="string",
     *                          nullable=true,
     *                          maxLength=255,
     *                      ),
     *                      @OA\Property (
     *                          property="notes",
     *                          type="string",
     *                          nullable=true,
     *                          maxLength=255,
     *                      ),
     *                  }
     *               )
     *          ),
     *          @OA\Property(
     *               property="email",
     *               type="string",
     *               format="email",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property(
     *               property="fax",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property(
     *               property="type_id",
     *               type="integer",
     *               nullable=false
     *          ),
     *          @OA\Property(
     *               property="timezone",
     *               type="string",
     *               nullable=false
     *          ),
     *          @OA\Property(
     *               property="working_hours",
     *               type="array",
     *               nullable=true,
     *               items=@OA\Items(
     *                   type="object",
     *                   required={"from", "to", "dayoff"},
     *                   properties={
     *                        @OA\Property (
     *                            property="from",
     *                            type="string",
     *                            format="time",
     *                            description="Date in format g:i A",
     *                            example="09:00 AM",
     *                            nullable=false
     *                        ),
     *                        @OA\Property (
     *                            property="to",
     *                            type="string",
     *                            format="time",
     *                            description="Date in format g:i A",
     *                            example="09:00 AM",
     *                            nullable=false
     *                        ),
     *                        @OA\Property (
     *                            property="dayoff",
     *                            type="boolean",
     *                            example=true,
     *                            nullable=false
     *                        )
     *                   }
     *               )
     *          ),
     *     }
     * )
     */
}
