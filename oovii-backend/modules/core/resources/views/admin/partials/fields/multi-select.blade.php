@php
    /**
     * @var $row \WezomCms\Core\Settings\Fields\AbstractField|\WezomCms\Core\Settings\Fields\ValuesListContainerTrait
     * @var $id string
     * @var $name string
     * @var $value mixed
     */
@endphp

@component('cms-core::admin.partials.fields.groupable', compact('row'))
    <select name="{{ $name }}[]" id="{{ $id }}" class="form-control {{ $row->getClass() }}" {!! $row->buildAttributes() !!} multiple>
        @foreach($row->getValuesList() as $listValue => $listName)
            <option value="{{ $listValue }}" {{ in_array($listValue, (array)old($name, $value)) ? 'selected' : null }}>@lang($listName)</option>
        @endforeach
    </select>
@endcomponent
