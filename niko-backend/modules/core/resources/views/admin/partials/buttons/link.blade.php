@php
    /**
     * @var $link \WezomCms\Core\Foundation\Buttons\Link
     */
@endphp
<a href="{{ $link->getLink() }}" class="{{ implode(' ', $link->getClasses()) }}"
   {!! $link->buildAttributes() !!}
   @if($link->getTitle() !== null) title="{{ $link->getTitle() }}" @endif
>
    @if($link->getIcon() !== null)
        <i class="fa {{ $link->getIcon() }}"></i> <span class="hidden-sm-down">{{ $link->getName() }}</span>
    @else
        {{ $link->getName() }}
    @endif
</a>
