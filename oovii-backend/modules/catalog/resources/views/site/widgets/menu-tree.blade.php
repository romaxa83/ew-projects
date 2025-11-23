@php
    /**
     * @var $categories \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Category[]
     */
@endphp
<nav>
    <ul>
        @foreach($categories as $category)
            <li>
                <a href="{{ $category->getFrontUrl() }}">{{ $category->name }}</a>
                @if($category->children->isNotEmpty())
                    <div>
                        <ul>
                            @foreach($category->children as $subCategory)
                                <li>
                                    <a href="{{ $subCategory->getFrontUrl() }}">
                                        @if($subCategory->imageExists(null, 'icon'))
                                            <img src="{{ $subCategory->getImageUrl(null, 'icon') }}" alt="">
                                        @endif
                                        <span>{{ $subCategory->name }}</span>
                                    </a>
                                    @if($subCategory->children->isNotEmpty())
                                        <ul>
                                            @foreach($subCategory->children as $subCategoryChild)
                                                <li>
                                                    <a href="{{ $subCategoryChild->getFrontUrl() }}">
                                                        {{ $subCategoryChild->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </li>
        @endforeach
        @if($showMore)
            <li>
                <a href="{{ route('catalog') }}">@lang('cms-catalog::site.Все категории')</a>
            </li>
        @endif
    </ul>
</nav>
