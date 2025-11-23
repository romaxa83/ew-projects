@langTabs
<div class="form-group">
    {!! Form::label($locale.'[name]', __('cms-catalog::admin.products.Name')) !!}
    {!! Form::text($locale.'[name]', old($locale.'.name', $obj->translateOrNew($locale)->name)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale . '[description]', __('cms-catalog::admin.products.Text')) !!}
    {!! Form::textarea($locale . '[description]', old($locale . '.description', $obj->translateOrNew($locale)->description), ['data-lang' => $locale]) !!}
{{--    {!! Form::textarea($locale . '[description]', old($locale . '.description', $obj->translateOrNew($locale)->description), ['class' => 'js-wysiwyg', 'data-lang' => $locale]) !!}--}}
</div>
<div class="form-group">
    {!! Form::label($locale.'[feature_1]', __('cms-catalog::admin.products.feature_1')) !!}
    {!! Form::text($locale.'[feature_1]', old($locale.'.feature_1', $obj->translateOrNew($locale)->feature_1)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale.'[feature_2]', __('cms-catalog::admin.products.feature_2')) !!}
    {!! Form::text($locale.'[feature_2]', old($locale.'.feature_2', $obj->translateOrNew($locale)->feature_2)) !!}
</div>
<div class="form-group">
    {!! Form::label($locale.'[feature_3]', __('cms-catalog::admin.products.feature_3')) !!}
    {!! Form::text($locale.'[feature_3]', old($locale.'.feature_3', $obj->translateOrNew($locale)->feature_3)) !!}
</div>
@endLangTabs
