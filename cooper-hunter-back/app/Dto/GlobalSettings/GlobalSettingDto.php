<?php

namespace App\Dto\GlobalSettings;

class GlobalSettingDto
{
    private string $footerAddress;
    private string $footerEmail;
    private string $footerPhone;
    private string $footerInstagramLink;
    private string $footerMetaLink;
    private string $footerTwitterLink;
    private string $footerYoutubeLink;
    private string $footerAdditionalEmail;
    private string $footerAppStoreLink;
    private string $footerGooglePayLink;
    private int $sliderCountdown;
    public ?string $companySite;
    public ?string $companyTitle;

    public static function buildByArgs(array $args): static
    {
        $instance = new static();

        $instance->footerAddress = $args['footer_address'];
        $instance->footerEmail = $args['footer_email'];
        $instance->footerPhone = $args['footer_phone'];
        $instance->footerInstagramLink = $args['footer_instagram_link'];
        $instance->footerMetaLink = $args['footer_meta_link'];
        $instance->footerTwitterLink = $args['footer_twitter_link'];
        $instance->footerYoutubeLink = $args['footer_youtube_link'];
        $instance->footerAdditionalEmail = $args['footer_additional_email'];
        $instance->footerAppStoreLink = $args['footer_app_store_link'];
        $instance->footerGooglePayLink = $args['footer_google_pay_link'];
        $instance->sliderCountdown = $args['slider_countdown'];
        $instance->companySite = $args['company_site'] ?? null;
        $instance->companyTitle = $args['company_title'] ?? null;

        return $instance;
    }

    public function getFooterAddress(): string
    {
        return $this->footerAddress;
    }

    public function getFooterEmail(): string
    {
        return $this->footerEmail;
    }

    public function getFooterPhone(): string
    {
        return $this->footerPhone;
    }

    public function getFooterInstagramLink(): string
    {
        return $this->footerInstagramLink;
    }

    public function getFooterMetaLink(): string
    {
        return $this->footerMetaLink;
    }

    public function getFooterTwitterLink(): string
    {
        return $this->footerTwitterLink;
    }

    public function getFooterYoutubeLink(): string
    {
        return $this->footerYoutubeLink;
    }

    public function getFooterAdditionalEmail(): string
    {
        return $this->footerAdditionalEmail;
    }

    public function getFooterAppStoreLink(): string
    {
        return $this->footerAppStoreLink;
    }

    public function getFooterGooglePayLink(): string
    {
        return $this->footerGooglePayLink;
    }

    public function getSliderCountdown(): int
    {
        return $this->sliderCountdown;
    }
}
