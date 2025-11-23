@php
    /**
     * @var $assetManager \WezomCms\Core\Contracts\Assets\AssetManagerInterface
     */

    $assetManager->addJs(
        'https://api-maps.yandex.ru/2.1/?' . http_build_query(['lang' => config('cms.core.translations.admin.locales.' . app()->getLocale() . '.js_locale'), 'apikey' => settings('settings.site.yandex_map_key')]),
        'yandex_map'
    )->once()->group(\WezomCms\Core\Contracts\Assets\AssetManagerInterface::GROUP_ADMIN);
@endphp
<div class="js-yandex-map" data-multiple="{{ $multiple ? 'true' : 'false' }}">
    @if(!$multiple)
        <div class="form-group">
            <input type="text"
                   class="js-search-input form-control"
                   placeholder="@lang('cms-core::admin.layout.Search address')">
        </div>
    @endif
    <input type="hidden" class="js-input" name="{{ $name }}" value="{{ json_encode($value) }}" {!! Html::attributes($attributes) !!}>
    <div class="js-map-container"
         data-config="{{ json_encode(['center' => $center]) }}"
         style="height: {{ $height }}px;"></div>
</div>
