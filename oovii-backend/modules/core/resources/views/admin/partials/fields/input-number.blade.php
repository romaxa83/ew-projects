@php
    /**
     * @var $row \WezomCms\Core\Settings\Fields\AbstractField
     * @var $id string
     * @var $name string
     * @var $value mixed
     */
@endphp

@component('cms-core::admin.partials.fields.groupable', compact('row'))
    <input id="{{ $id }}" type="number" class="form-control {{ $row->getClass() }}" {!! $row->buildAttributes() !!}
           name="{{ $name }}" value="{{ str_replace(',', '.', $value) }}"
           step="{{ str_replace(',', '.', $row->getStep()) ?: 1 }}">
@endcomponent
