@php
    /**
     * @var $categories \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Category[]
     */
    $menu = $categories->chunk(7);
@endphp
<nav>
    @foreach($menu as $menuChunk)
        <ul>
            @foreach($menuChunk as $item)
                <li>
                    <a href="{{ $item->getFrontUrl() }}">{{ $item->name }}</a>
                </li>
            @endforeach
        </ul>
    @endforeach
</nav>
