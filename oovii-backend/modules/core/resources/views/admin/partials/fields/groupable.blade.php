@php
    /**
     * @var $row \WezomCms\Core\Settings\Fields\AbstractField
     */
@endphp
@if($row->hasToBeGrouped())
    <div class="input-group">
        @if($row->getButtonBefore())
            <span class="input-group-prepend">
                {!! $row->getButtonBefore()->render() !!}
            </span>
        @endif
        @if($row->getIconBefore())
            <span class="input-group-prepend">
                <span class="input-group-text"><i class="fa {{ $row->getIconBefore() }}"></i></span>
            </span>
        @endif
        {!! $slot !!}
        @if($row->getIconAfter())
            <span class="input-group-append">
                <span class="input-group-text"><i class="fa {{ $row->getIconAfter() }}"></i></span>
            </span>
        @endif
        @if($row->getButtonAfter())
            <span class="input-group-append">
                {!! $row->getButtonAfter()->render() !!}
            </span>
        @endif
    </div>
@else
    {!! $slot !!}
@endif
