@php
    /**
     * @var $searchForm iterable
     */
@endphp
<div>
    @foreach ($searchForm as $group)
        @php
            $allDisabled = true;
            $hasDisabled = false;
            foreach ($group['options'] ?? [] as $option) {
                if ($option['disabled']) {
                    $hasDisabled = true;
                } else {
                    $allDisabled = false;
                }
            }
        @endphp
        @if($group['type'] === \WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_HIDDEN)
            <input type="hidden" name="{{ $group['name'] }}" value="{{ $group['value'] }}">
        @else
            @switch($group['type'])
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_RADIO)
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_CHECKBOX)
                <div>
                    <div>{{ $group['title'] }}</div>
                    <div>
                        @foreach($group['options'] as $option)
                            <div {!! $option['disabled'] && ! $allDisabled ? 'hidden' : '' !!}>
                                <label>
                                    <input type="{{ $group['type'] === \WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_RADIO ? 'radio' : 'checkbox' }}"
                                           name="{{ $option['input_name'] ?? $group['name'] }}"
                                           value="{{ $option['value'] }}"
                                           {{ $option['selected'] ? 'checked' : '' }} {{ $option['disabled'] ? 'disabled' : '' }}>
                                    <span>{{ $option['name'] }}</span>
                                    @if(!$option['disabled'] && !$option['selected'])
                                        <a href="{{ $option['url'] }}"></a>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                        @if(!$allDisabled && $hasDisabled)
                            <div>@lang('cms-catalog::site.Все')</div>
                        @endif
                    </div>
                </div>
                @break
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_NUMBER_RANGE)
                @if($group['from']['min'] && $group['to']['max'])
                    <div>{{ $group['title'] }}</div>
                    <div>
                        <div>@lang('cms-catalog::site.products.from')</div>
                        <input type="number"
                               name="{{ $group['from']['name'] }}"
                               value="{{ $group['from']['value'] ?? $group['from']['min'] ?? '' }}"
                               placeholder="{{ $group['from']['placeholder'] ?? '' }}"
                               min="{{ $group['from']['min'] ?? '' }}"
                        />
                        <div>@lang('cms-catalog::site.products.to')</div>
                        <input type="number"
                               name="{{ $group['to']['name'] }}"
                               value="{{ $group['to']['value'] ?? $group['to']['max'] ?? '' }}"
                               placeholder="{{ $group['to']['placeholder'] ?? '' }}"
                               max="{{ $group['to']['max'] ?? '' }}"
                        />
                        <div>{{ $group['currency'] ?? '' }}</div>
                        <button>ок</button>
                    </div>
                    <label>
                        <input type="hidden"
                               data-min="{{ $group['from']['min'] ?? 0 }}"
                               data-max="{{ $group['to']['max'] ?? 0 }}"
                               data-from="{{ $group['from']['value'] ?? $group['from']['min'] ?? 0 }}"
                               data-to="{{ $group['to']['value'] ?? $group['to']['max'] ?? 0 }}"/>
                    </label>
                @endif
                @break
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_CHECKBOX_WITH_ICON)
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_RADIO_WITH_ICON)
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_SELECT)
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_SELECT_RANGE)
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_NUMBER)
                @case(\WezomCms\Catalog\Filter\Contracts\FilterFormBuilder::TYPE_DOUBLE)
                @break
            @endswitch
        @endif
    @endforeach
</div>
