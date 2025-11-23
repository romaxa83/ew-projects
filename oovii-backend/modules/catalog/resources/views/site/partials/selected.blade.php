@php
    /**
     * @var $selected \Illuminate\Support\Collection
     * @var $baseUrl string
     * @var $resetUrl string|null
     * @var $queryParams array|null
     */
@endphp
<div>
    @if($selected->isNotEmpty())
        <div>
            <ul>
                <li>@lang('cms-catalog::site.Обрано') {{ $products->total() }} {{ trans_choice('cms-catalog::site.товар|товара|товарів', $products->total()) }}:</li>
                @foreach($selected as $item)
                    <li>
                        <a href="{{ $item['removeUrl'] }}">
                            <span>{{ $item['name'] }}</span>
                            @svg('common', 'remove', 10)
                        </a>
                    </li>
                @endforeach
                @if($selected->count())
                    <li>
                        <a href="{{ $resetUrl ?? $baseUrl }}" rel="nofollow">
                            <span>@lang('cms-catalog::site.Сбросить все')</span>
                            @svg('common', 'remove', 10)
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
    <div>
        <div>@lang('cms-catalog::site.Сортировать по'):</div>
        <select class="js-import"
                name="{{ $sort->getUrlKey() }}"
                data-params="{{ $queryParams ?? false ? json_encode($queryParams) : null }}"
                data-select-sort>
            @foreach($sort->getAllSortVariants() as $key => $name)
                <option value="{{ str_replace('default', '', $key) }}" {{ $sort->isThisSort($key) ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>
