@php
    /**
     * @var $categories \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Category[][]
     * @var $currentCategory \WezomCms\Catalog\Models\Category|null
     */
@endphp
<ul>
    @foreach($categories[null] ?? [] as $category)
        @include('cms-catalog::site.partials.expanded-multiple-category-row')
    @endforeach
</ul>
