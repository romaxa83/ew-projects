@php
    /**
     * @var $obj \Illuminate\Database\Eloquent\Model|\WezomCms\Core\ExtendPackage\Translatable
     * @var $locale string|null
     */
    $obj = method_exists($obj, 'translateOrNew') ? $obj->translateOrNew($locale) : $obj;
@endphp
<div class="form-group">
    {!! Form::label($locale . '[title]', __('cms-core::admin.seo.Title')) !!}
    {!! Form::text($locale . '[title]', old($locale . '.title', $obj->title)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[h1]', __('cms-core::admin.seo.H1')) !!}
    {!! Form::text($locale . '[h1]', old($locale . '.h1', $obj->h1)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[keywords]', __('cms-core::admin.seo.Keywords')) !!}
    {!! Form::textarea($locale . '[keywords]', old($locale . '.keywords', $obj->keywords), ['rows' => 3]) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[description]', __('cms-core::admin.seo.Description')) !!}
    {!! Form::textarea($locale . '[description]', old($locale . '.description', $obj->description), ['rows' => 3]) !!}
</div>
