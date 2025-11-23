@php
    /**
     * @var $name string
     * @var $value string|null
     * @var $attributes array
     */
    $source = array_get($attributes, 'source');
    if (isset($attributes['source'])) {
        unset($attributes['source']);
    }

    $class = array_merge(['form-control'], (array)array_get($attributes, 'class', []));
    if (optional(Form::getModel())->exists === false) {
        $class[] = 'js-live-slug';
    }

    $attributes = array_merge([
        'type' => 'text',
        'name' => $name,
        'value' => $value,
        'id' => Form::getIdAttribute($name, $attributes),
        'autocomplete' => 'off',
        'class' => $class,
    ], $attributes);
@endphp
<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="@lang('cms-core::admin.layout.Canonical URL')"></i>
<div class="input-group js-slug-generator" data-source="{{ $source }}">
    <input {!! Html::attributes($attributes) !!} />
    <div class="input-group-append">
        <button class="btn btn-outline-secondary"
                type="button"
                title="@lang('cms-core::admin.layout.Generate Slug')"><i class="fa fa-cogs"></i></button>
    </div>
</div>
