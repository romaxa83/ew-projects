@php
    /**
     * @var $routeName string
     * @var $forceDelete bool|null
     * @var $deleteText string|null
     * @var $buttons array|null
     */
    $uniqueId = uniqid('mass-check');
    $forceDelete = $forceDelete ?? false;
@endphp
<div class="list-control-pane">
    <div class="custom-control custom-checkbox d-inline-block">
        <input type="checkbox" class="custom-control-input" id="select-all-list-{{ $uniqueId }}" data-select-all-list>
        <label class="custom-control-label" for="select-all-list-{{ $uniqueId }}" title="@lang('cms-core::admin.layout.Check/Uncheck all')"></label>
    </div>
    <div class="control-actions" data-list-actions>
        @foreach($buttons ?? [] as $button)
            {!! $button !!}
        @endforeach
        @if($forceDelete)
            <button class="btn btn-sm btn-warning"
                    data-route="{{ route($routeName . '.mass-restore') }}"
                    data-list-action="restore"
                    title="@lang('cms-core::admin.layout.Restore')">
                <i class="fa fa-undo"></i>
            </button>
        @endif
        <button class="btn btn-sm btn-danger"
                data-route="{{ route($routeName . '.mass-delete', ['force_delete' => $forceDelete]) }}"
                data-list-action="delete"
                @if($deleteText ?? false)data-confirm-text="{{ $deleteText }}"@endif
                title="@lang('cms-core::admin.layout.Delete selected items')">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
