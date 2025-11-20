@php
use Illuminate\Database\Eloquent\Model;
/**
 * @var $obj Model
 */
$gate = (property_exists($obj, 'abilityPrefix') ? $obj->abilityPrefix : str_replace('_', '-', $obj->getTable())) . '.delete';

$uniqueId = uniqid('mass-check');
@endphp
<div class="list-item-check custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" name="IDS[]" id="list-item-{{ $uniqueId }}"
           data-list-item value="{{ $obj->id }}" @cannot($gate, $obj) disabled @endcannot>
    <label class="custom-control-label" for="list-item-{{ $uniqueId }}"></label>
</div>
