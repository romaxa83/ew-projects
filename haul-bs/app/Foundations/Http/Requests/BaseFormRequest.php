<?php

namespace App\Foundations\Http\Requests;

use App\Foundations\Rules\PasswordRule;
use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;

 class BaseFormRequest extends FormRequest
{
     public function authorize(): bool
     {
         return true;
     }

    public function paginationRule(): array
    {
        return [
            'page' => ['nullable', 'int'],
            'per_page' => ['nullable', 'int'],
        ];
    }

     public function searchRule(): array
     {
         return [
             'search' => ['nullable', 'string', 'min:3'],
         ];
     }

     public function idRule(): array
     {
         return [
             'id' => ['nullable', 'numeric'],
         ];
     }

     public function orderRule(array $allowed): array
     {
         return [
             'order_by' => ['nullable', 'string', $this->orderByIn($allowed)],
             'order_type' => ['nullable', 'string', $this->orderTypeIn()],
         ];
     }

     public function passwordRule(): array
     {
         return [
             'password' => ['required', 'min:'.User::MIN_LENGTH_PASSWORD, 'max:32', new PasswordRule()],
             'password_confirmation' => ['required','same:password','min:'.User::MIN_LENGTH_PASSWORD, 'max:32']
         ];
     }

     public function fileRule(): array
     {
         return [
             'attachment' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,jpe,doc,docx,txt,xls,xlsx'],
         ];
     }

     public function attachmentMimes(): string
     {
         return 'mimes:pdf,png,jpg,jpeg,jpe,doc,docx,txt,xls,xlsx';
     }

     public function imageRule(): array
     {
         return ['required', 'image', "max:" . byte_to_kb(config('media-library.max_file_size'))];
     }

     protected function orderByIn(array $allowed): string
     {
         return 'in:' . implode(',', $allowed);
     }

     protected function orderTypeIn(): string
     {
         return 'in:' . implode(',', ['asc', 'desc']);
     }
}
