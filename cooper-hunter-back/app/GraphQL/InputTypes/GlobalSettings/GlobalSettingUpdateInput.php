<?php

namespace App\GraphQL\InputTypes\GlobalSettings;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class GlobalSettingUpdateInput extends BaseInputType
{
    public const NAME = 'GlobalSettingUpdateInput';

    public function fields(): array
    {
        return [
            'footer_address' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_phone' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_instagram_link' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_meta_link' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_twitter_link' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_youtube_link' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_additional_email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_app_store_link' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'footer_google_pay_link' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'slider_countdown' => [
                'type' => NonNullType::int(),
                'rules' => ['integer', 'min:1', 'max:255'],
            ],
            'company_site' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
            'company_title' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string'],
            ],
        ];
    }
}
