@php
    /**
     * @var $categories \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Category[]
     */
@endphp
<div>
    @foreach($categories as $category)
        <a href="{{ $category->getFrontUrl() }}">
            @if($category->imageExists(null, 'icon'))
                <img src="{{ $category->getImageUrl(null, 'icon') }}" alt="{{ $category->name }}">
            @endif
            <span>{{ $category->name }}</span>
        </a>
    @endforeach
</div>
