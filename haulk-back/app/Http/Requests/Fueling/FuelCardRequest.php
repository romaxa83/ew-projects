<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Tags\Tag;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FuelCardRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        /** @var FuelCard $fuelCard */
        $fuelCard = $this->route('fuelCard');
        $provider = $this->input('provider');

        $fuelCardUniqueRule = Rule::unique(FuelCard::TABLE_NAME, 'card')->where('provider', $provider);
        if ($fuelCard) {
            $fuelCardUniqueRule->ignore($fuelCard->id);
        }
        switch ($provider) {
            case FuelCardProviderEnum::EFS;
                $size = 'size:5';
                break;
            case FuelCardProviderEnum::QUIKQ;
                $size = 'size:6';
                break;
            default;
                $size = 'size:5';
                break;
        }
        return [
            'card' => ['required', 'string', $size, $fuelCardUniqueRule],
            'provider' => ['required', 'string', FuelCardProviderEnum::ruleIn()],
            'status' => ['required', 'string', FuelCardStatusEnum::ruleIn()],
        ];
    }
}
