@php
    /**
     * @var $obj \Illuminate\Database\Eloquent\Model
     */
        $field = $field ?? 'published';
        $isPublished = $obj->{$field};
        $request = $request ?? '';

        switch ($field) {
            case 'active':
                $textOn = $textOn ?? __('cms-core::admin.layout.Active');
                $textOff = $textOff ?? __('cms-core::admin.layout.Inactive');
                break;
            case 'read':
                $textOn = $textOn ?? __('cms-core::admin.layout.Read');
                $textOff = $textOff ?? __('cms-core::admin.layout.Unread');
                break;
            case 'published':
            default:
                $textOn = $textOn ?? __('cms-core::admin.layout.Published');
                $textOff = $textOff ?? __('cms-core::admin.layout.Unpublished');
        }
@endphp
@can(app(\WezomCms\Core\Contracts\PermissionsContainerInterface::class)->getModelAbility($obj, 'edit'), $obj)
    <div class="btn-group js-status-switcher" data-type="small" data-text-on="{{ $textOn }}"
         data-text-off="{{ $textOff }}"
         data-model="{{ encrypt(get_class($obj)) }}" data-model-request="{{ $request ? encrypt($request) : '' }}"
         role="group">
        <button type="button" class="btn btn-{{ $isPublished ? 'success' : 'outline-secondary' }}"
                data-id="{{ $obj->id }}" data-status="{{ $isPublished }}" data-field="{{ $field }}"
                title="{{ $isPublished ? $textOn : $textOff }}" data-toggle="tooltip" data-placement="top"
        ><i class="fa {{ $isPublished ? 'fa-check-square-o' : 'fa-square-o' }}"></i>
        </button>
    </div>
@else
    <div class="btn-group" role="group">
        <button class="btn btn-{{ $isPublished ? 'success' : 'outline-secondary' }}" disabled="disabled"
                title="{{ $isPublished ? $textOn : $textOff }}" data-toggle="tooltip" data-placement="top"
        ><i class="fa {{ $isPublished ? 'fa-check-square-o' : 'fa-square-o' }}"></i>
        </button>
    </div>
@endcan
