<?php

namespace App\Services\GlobalSettings;

use App\Dto\GlobalSettings\GlobalSettingDto;
use App\Models\GlobalSettings\GlobalSetting;

class GlobalSettingService
{
    public function create(GlobalSettingDto $dto): GlobalSetting
    {
        $globalSetting = new GlobalSetting();

        $this->fill($dto, $globalSetting);

        $globalSetting->save();

        return $globalSetting;
    }

    protected function fill(GlobalSettingDto $dto, GlobalSetting $globalSetting): void
    {
        $globalSetting->footer_address = $dto->getFooterAddress();
        $globalSetting->footer_email = $dto->getFooterEmail();
        $globalSetting->footer_phone = $dto->getFooterPhone();
        $globalSetting->footer_instagram_link = $dto->getFooterInstagramLink();
        $globalSetting->footer_meta_link = $dto->getFooterMetaLink();
        $globalSetting->footer_twitter_link = $dto->getFooterTwitterLink();
        $globalSetting->footer_youtube_link = $dto->getFooterYoutubeLink();
        $globalSetting->footer_additional_email = $dto->getFooterAdditionalEmail();
        $globalSetting->footer_app_store_link = $dto->getFooterAppStoreLink();
        $globalSetting->footer_google_pay_link = $dto->getFooterGooglePayLink();
        $globalSetting->slider_countdown = $dto->getSliderCountdown();
        $globalSetting->company_site = $dto->companySite;
        $globalSetting->company_title = $dto->companyTitle;
    }

    public function update(GlobalSetting $globalSetting, GlobalSettingDto $dto): GlobalSetting
    {
        $this->fill($dto, $globalSetting);

        $globalSetting->save();

        return $globalSetting;
    }
}
