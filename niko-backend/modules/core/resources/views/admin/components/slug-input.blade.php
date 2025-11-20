@php
    $source = array_get($attributes, 'source');
    if (isset($attributes['source'])) {
        unset($attributes['source']);
    }
@endphp
<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="@lang('cms-core::admin.layout.Canonical URL')"></i>
<div class="input-group js-slug-generator" data-source="{{ $source }}">
    <input name="{{ $name }}"
           value="{{ $value }}"
           id="{{ Form::getIdAttribute($name, $attributes) }}"
           class="form-control {{ optional(Form::getModel())->exists === false ? 'js-live-slug' : '' }}"
           autocomplete="off"/>
    <div class="input-group-append">
        <button class="btn btn-outline-secondary"
                type="button"
                title="@lang('cms-core::admin.layout.Generate Slug')"><i class="fa fa-cogs"></i></button>
    </div>
</div>
