<?php

namespace App\Services\Commercial;

use App\Models\Commercial\CommercialSettings;

class CommercialSettingsService
{
    public function createOrUpdate(array $args): CommercialSettings
    {
        $settings = CommercialSettings::firstOrNew();

        $settings->nextcloud_link = $args['nextcloud_link'] ?? $settings->nextcloud_link;
        $settings->quote_title = $args['quote_title'] ?? $settings->quote_title;
        $settings->quote_address_line_1 = $args['quote_address_line_1'] ?? $settings->quote_address_line_1;
        $settings->quote_address_line_2 = $args['quote_address_line_2'] ?? $settings->quote_address_line_2;
        $settings->quote_phone = $args['quote_phone'] ?? $settings->quote_phone;
        $settings->quote_email = $args['quote_email'] ?? $settings->quote_email;
        $settings->quote_site = $args['quote_site'] ?? $settings->quote_site;

        $settings->save();

        return $settings;
    }
}
