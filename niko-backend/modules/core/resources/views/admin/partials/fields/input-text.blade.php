@php
    /**
     * @var $row \WezomCms\Core\Settings\Fields\AbstractField
     * @var $id string
     * @var $name string
     * @var $value mixed
     */
@endphp

@component('cms-core::admin.partials.fields.groupable', compact('row'))
    <input id="{{ $id }}" type="{{ str_replace('input', 'text', $row->getType()) }}" {!! $row->buildAttributes() !!}
           class="form-control {{ $row->getClass() }}" name="{{ $name }}" value="{{ old($name, $value) }}">
@endcomponent
