<?php

namespace App\Http\Request\Report;

use Illuminate\Foundation\Http\FormRequest;

class AttachVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'video' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm'
            'video' => ['required', 'file', 'min:3', 'max:300000']
        ];
    }
}
