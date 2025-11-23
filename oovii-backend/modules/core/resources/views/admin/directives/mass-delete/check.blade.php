@php

use Illuminate\Database\Eloquent\Model;

/**
 * @var $obj Model
 */

$uniqueId = uniqid('mass-check');
@endphp
<div class="list-item-check custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" name="IDS[]" id="list-item-{{ $uniqueId }}"
           data-list-item value="{{ $obj->id }}"
           @cannot(app(\WezomCms\Core\Contracts\PermissionsContainerInterface::class)->getModelAbility($obj, 'delete'), $obj) disabled @endcannot>
    <label class="custom-control-label" for="list-item-{{ $uniqueId }}"></label>
</div>
