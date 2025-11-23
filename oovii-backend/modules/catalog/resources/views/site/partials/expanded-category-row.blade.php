@php
    /**
     * @var $categories \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Category[][]
     * @var $category null|\WezomCms\Catalog\Models\Category
     * @var $currentCategory null|\WezomCms\Catalog\Models\Category
     */
@endphp
<li>
    @if($currentCategory && $currentCategory->id === $category->id)
        <span class="is-active">{{ $category->name }}</span>
    @else
        <a href="{{ $category->getFrontUrl() }}" title="{{ $category->name }}"
           class="{{ !empty($categories[$category->id]) ? 'is-active' : null }}">{{ $category->name }}</a>
    @endif
    @if(!empty($categories[$category->id]))
        <ul>
            @foreach($categories[$category->id] ?? [] as $subCategory)
                @include('cms-catalog::site.widgets.partials.expanded-category-row', ['category' => $subCategory])
            @endforeach
        </ul>
    @endif
</li>
