<?php


namespace App\Rules\Chat;


use App\Models\Chat\ChatMenu;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class SubMenuRule implements Rule, DataAwareRule
{
    private array $data;

    public function passes($attribute, $value): bool
    {
        return ChatMenu::query()
            ->whereIn('id', $value)
            ->where('parent_id', $this->data['id'] ?? null)
            ->exists();
    }

    public function setData($data): SubMenuRule
    {
        $this->data = $data;

        return $this;
    }

    public function message(): string
    {
        return trans('validation.custom.chat_menu.incorrect_sub_menu');
    }
}
