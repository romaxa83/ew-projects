@if($item->divider)
    <li class="nav-devider"></li>
    <li class="nav-label">{{ $item->title }}</li>
@endif
<li @if($item->isActive) class="active" @endif>
    @if($item->hasChildren())
        <a class="has-arrow @if($item->badge)has-badge @endif" href="#" aria-expanded="false"
           title="{{ $item->title }}">
            @if($item->icon)<i class="fa {{ $item->icon }}"></i> @endif
            <span class="{{ $root ? 'hide-menu d-inline-block align-middle' : null }} nav-item-text" data-search-i>{{ $item->title }}</span>
            @if($item->badge) <span class="label label-rouded label-{{ $item->badge_type ?? 'primary' }}">{{ $item->badge }}</span>@endif
        </a>
        <ul
                @if($item->isActive)
                aria-expanded="true" class="collapse show"
                @else
                aria-expanded="false" class="collapse"
                @endif
        >
            @foreach($item->children() as $child)
                @include('cms-core::admin.partials.sidebar-menu-item', ['item' => $child, 'parent' => $child, 'root' => false])
            @endforeach
        </ul>
    @else
        <a href="{{ $item->url() }}"
           title="{{ $item->title }}"
           @if($item->badge) class="has-badge" @endif
        >
            @if($item->icon)<i class="fa {{ $item->icon }}"></i> @endif
            <span class="{{ $root ? 'hide-menu d-inline-block align-middle' : null }} nav-item-text" data-search-i>{{ $item->title }}</span>
            @if($item->badge) <span class="label label-rouded label-{{ $item->badge_type ?? 'primary' }}">{{ $item->badge }}</span>@endif
        </a>
    @endif
</li>
