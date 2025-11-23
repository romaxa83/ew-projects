@php
    /**
     * @var $categories \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Category[][]
     * @var $category \WezomCms\Catalog\Models\Category
     * @var $currentCategory \WezomCms\Catalog\Models\Category|null
     */
    $query = http_build_query(array_merge(app('request')->query(), ['category' => $category->slug]));

@endphp
<li>
    @if($currentCategory && $currentCategory->id === $category->id)
        <span class="is-active">{{ $category->name }}</span>
    @else
        <a href="{{ url()->current() . ($query ? '?' . $query : '') }}" title="{{ $category->name }}"
           class="{{ !empty($categories[$category->id]) ? 'is-active' : null }}">{{ $category->name }}</a>
    @endif
    @if(!empty($categories[$category->id]))
        <ul>
            @foreach($categories[$category->id] ?? [] as $subCategory)
                @include('cms-catalog::site.widgets.partials.expanded-multiple-category-row', ['category' => $subCategory])
            @endforeach
        </ul>
    @endif
</li>
