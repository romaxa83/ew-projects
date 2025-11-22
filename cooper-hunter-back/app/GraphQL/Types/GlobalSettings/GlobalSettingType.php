<?php

namespace App\GraphQL\Types\GlobalSettings;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\GlobalSettings\GlobalSetting;
use GraphQL\Type\Definition\Type;

class GlobalSettingType extends BaseType
{
    public const NAME = 'GlobalSetting';
    public const MODEL = GlobalSetting::class;

    public function fields(): array
    {
        return [
            'footer_address' => [
                'type' => NonNullType::string(),
            ],
            'footer_email' => [
                'type' => NonNullType::string(),
            ],
            'footer_phone' => [
                'type' => NonNullType::string(),
            ],
            'footer_instagram_link' => [
                'type' => NonNullType::string(),
            ],
            'footer_meta_link' => [
                'type' => NonNullType::string(),
            ],
            'footer_twitter_link' => [
                'type' => NonNullType::string(),
            ],
            'footer_youtube_link' => [
                'type' => NonNullType::string(),
            ],
            'footer_additional_email' => [
                'type' => NonNullType::string(),
            ],
            'footer_app_store_link' => [
                'type' => NonNullType::string(),
            ],
            'footer_google_pay_link' => [
                'type' => NonNullType::string(),
            ],
            'slider_countdown' => [
                'type' => NonNullType::int(),
            ],
            'company_site' => [
                'type' => Type::string(),
            ],
            'company_title' => [
                'type' => Type::string(),
            ],
        ];
    }
}
