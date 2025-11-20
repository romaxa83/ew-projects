@langTabs
<div class="form-group">
    {!! Form::label($locale . '[name]', __('cms-core::admin.layout.Name')) !!}
    {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[address]', __('cms-dealerships::admin.Address')) !!}
    {!! Form::text($locale . '[address]', old($locale . '.address', $obj->translateOrNew($locale)->address)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[text]', __('cms-dealerships::admin.Text')) !!}
    {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[services]', __('cms-dealerships::admin.Services')) !!}
    {!! Form::textarea($locale . '[services]', old($locale . '.services', $obj->translateOrNew($locale)->services)) !!}
</div>
@endLangTabs
{{--@dd(old('phones', $obj->phones));--}}
<div class="form-group">
    {!! Form::PhoneWithDesc(old('phones', $obj->phones), __('cms-dealerships::admin.Phones')) !!}
</div>
