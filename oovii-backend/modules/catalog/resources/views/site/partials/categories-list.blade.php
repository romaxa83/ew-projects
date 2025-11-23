@php
    /**
     * @var $result \Illuminate\Pagination\LengthAwarePaginator|\WezomCms\Catalog\Models\Category[]
     */
@endphp

@foreach($result as $item)
    <a href="{{ $item->getFrontUrl() }}">
        <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">
        <span>{{ $item->name }}</span>
    </a>
@endforeach
