@php
    /**
     * @var $button \WezomCms\Core\Foundation\Buttons\Button
     */
@endphp
<button class="{{ implode(' ', $button->getClasses()) }}" {!! $button->buildAttributes() !!}
@if($button->getTitle() !== null) title="{{ $button->getTitle() }}" @endif
>
    @if($button->getIcon() !== null)
        <i class="fa {{ $button->getIcon() }}"></i> <span class="hidden-sm-down">{{ $button->getName() }}</span>
    @else
        {{ $button->getName() }}
    @endif
</button>
