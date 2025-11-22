<?php


namespace App\Rules\Tires;


use App\Models\Dictionaries\TireChangesReason;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class TireMutationRule implements Rule, DataAwareRule
{
    private array $data;

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (empty($this->data['id'])) {
            return true;
        }

        if (isBackOffice()) {
            return true;
        }

        if (empty($value)) {
            return false;
        }

        if (!TireChangesReason::find($value)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return trans('validation.required', ['attribute' => 'change reason id']);
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
