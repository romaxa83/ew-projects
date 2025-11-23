@php
    /**
     * @var $result \Illuminate\Support\Collection|WezomCms\Catalog\Models\Product[]
     * @var $query string
     */
@endphp
<ul>
    @foreach($result as $item)
        @if($item instanceof \WezomCms\Catalog\Models\Product)
            <li>
                <a href="{{ $item->getFrontUrl() }}" title="{{ $item->name }}">
                    <img src="{{ $item->getImageUrl() }}" alt="{{ $item->image_alt }}" title="{{ $item->image_title }}">
                    <span>{{ $item->name }}</span>
                    <span>@money($product->cost, true)</span>
                </a>
            </li>
        @elseif($item instanceof \WezomCms\Catalog\Models\Category)
            <li>
                <a href="{{ $item->getFrontUrl() }}">{{ $item->name }}</a>
            </li>
        @endif
    @endforeach
</ul>
<a href="{{ route('search', ['search' => $query]) }}">
    @lang('cms-catalog::site.Все результаты')
</a>
