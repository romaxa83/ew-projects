@php
    /**
     * @var $link \WezomCms\Core\Foundation\Buttons\DropDownLinks
     */
    $links = $link->getLinks();

    $first = array_slice($links, 0, 1);
    $name = key($first);
    $firstUrl = array_shift($first);
@endphp
<div class="btn-group">
    <a href="{{ $firstUrl }}" class="{{ implode(' ', $link->getClasses()) }}"
       {!! $link->buildAttributes() !!}
    @if($link->getTitle() !== null) title="{{ $link->getTitle() }}" @endif>
        @if($link->getIcon() !== null)
            <i class="fa {{ $link->getIcon() }}"></i> <span class="hidden-sm-down">{{ $name }}</span>
        @else
            {{ $name }}
        @endif
    </a>
    @if(count($links) > 1)
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
        <div class="dropdown-menu">
            @foreach(array_slice($links, 1) as $name => $url)
                <a class="dropdown-item" href="{{ $url }}" {!! $link->buildAttributes() !!}>{{ $name }}</a>
            @endforeach
        </div>
    @endif
</div>
