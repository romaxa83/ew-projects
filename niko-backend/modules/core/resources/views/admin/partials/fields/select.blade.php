@php
    /**
     * @var $row \WezomCms\Core\Settings\Fields\AbstractField|\WezomCms\Core\Settings\Fields\ValuesListContainerTrait
     * @var $id string
     * @var $name string
     * @var $value mixed
     */
@endphp

@component('cms-core::admin.partials.fields.groupable', compact('row'))
    <select name="{{ $name }}" id="{{ $id }}" class="form-control {{ $row->getClass() }}" {!! $row->buildAttributes() !!}>
        @foreach($row->getValuesList() as $listValue => $listName)
            <option value="{{ $listValue }}" {{ $listValue == old($name, $value) ? 'selected' : '' }}>@lang($listName)</option>
        @endforeach
    </select>
@endcomponent
