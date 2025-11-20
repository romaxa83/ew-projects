@php
/**
 * @var $obj \WezomCms\Core\Traits\ImageMultiUploaderAttachable
 */
@endphp
{!! Form::open(['url' => route('admin.image-multi-uploader.rename', [$obj->id, 'model' => encrypt($model)]), 'class' => 'js-ajax-form js-force-valid']) !!}
<div class="modal-header">
    <h5 class="modal-title" id="edit-file-name-modal-{{ sha1($model) }}-label">@lang('cms-core::admin.layout.Rename')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    @foreach(array_except($obj->customPopupFields(), 'translatable') as $field)
        @php
            $attributes = array_get($field, 'attributes', []);
            Form::addClass($attributes, 'js-ignore');
        @endphp
        <div class="form-group">
            {!! Form::label($field['name'], $field['label']) !!}
            {!! Form::{$field['type'] ?? 'text'}($field['name'], old($field['name'], $obj->{$field['name']}), $attributes) !!}
        </div>
    @endforeach
    @langTabs
        @if($obj->hasNameField())
            <div class="form-group">
                {!! Form::label($locale . '[name]', __('cms-core::admin.layout.Name')) !!}
                {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name), ['class' => 'js-ignore']) !!}
            </div>
        @endif
        @if($obj->hasAltAndTitleFields())
            <div class="form-group">
                {!! Form::label($locale . '[alt]', __('cms-core::admin.layout.Alt')) !!}
                {!! Form::text($locale . '[alt]', old($locale . '.alt', $obj->translateOrNew($locale)->alt), ['class' => 'js-ignore']) !!}
            </div>
            <div class="form-group">
                {!! Form::label($locale . '[title]', __('cms-core::admin.layout.Title')) !!}
                {!! Form::text($locale . '[title]', old($locale . '.title', $obj->translateOrNew($locale)->title), ['class' => 'js-ignore']) !!}
            </div>
        @endif
        @foreach(array_get($obj->customPopupFields(), 'translatable', []) as $field)
            @php
                $attributes = array_get($field, 'attributes', []);
                Form::addClass($attributes, 'js-ignore');
            @endphp
            <div class="form-group">
                {!! Form::label("{$locale}[{$field['name']}]", $field['label']) !!}
                {!! Form::{$field['type'] ?? 'text'}("{$locale}[{$field['name']}]", old("{$locale}.{$field['name']}", $obj->translateOrNew($locale)->{$field['name']}), $attributes) !!}
            </div>
        @endforeach
    @endLangTabs
    <script>
        window.inits.hideLangTabs();
        window.inits.simpleAjaxFormSubmit();
        window.inits.forceValid();
    </script>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary"
            data-dismiss="modal">@lang('cms-core::admin.layout.Cancel')</button>
    <button type="submit" class="btn btn-primary">@lang('cms-core::admin.layout.Save')</button>
</div>
{!! Form::close() !!}
