<?php

namespace App\Http\Requests\Communications;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'conversation' => ['array'],
            'conversation.id' => ['nullable'],
            'conversation.client' => ['nullable', 'array'],
            'conversation.client.id' => ['nullable', Rule::exists(Client::TABLE, 'id')],
            'conversation.channelContact' => ['nullable', 'string'],
            'conversation.type' => ['nullable', 'string'],
        ];
    }
}
