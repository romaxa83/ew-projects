<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @langTabs
                <div class="form-group">
                    {!! Form::label($locale . '[name]', __('cms-core::admin.layout.Name')) !!}
                    {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
                </div>
                <div class="form-group">
                    {!! Form::label($locale . '[text]', __('cms-dealerships::admin.Text')) !!}
                    {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text)) !!}
                </div>
                <div class="form-group">
                    {!! Form::label($locale . '[link]', __('cms-promotions::admin.Link')) !!}
                    {!! Form::text($locale . '[link]', old($locale . '.link', $obj->translateOrNew($locale)->link)) !!}
                </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label(str_slug('published'), __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published', old('published', $obj->exists ? $obj->published : true))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('type', __('cms-promotions::admin.Type')) !!}
                    <div class="input-group">
                        {!! Form::select('type', $obj::getTypeBySelect(), old('type', $obj->type), ['class' => 'js-select2']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('code_1c', __('cms-promotions::admin.Code 1c')) !!}
                    {!! Form::text('code_1c', old('code_1c', $obj->code_1c))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('image', __('cms-promotions::admin.Image')) !!}
                    {!! Form::imageUploader('image', $obj, route($routeName . '.delete-image', $obj->id)) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('image', __('cms-promotions::admin.Image for ua')) !!}
                    {!! Form::imageUploader('image_ua', $obj, route($routeName . '.delete-image',  ['id' => $obj->id, 'field' => 'image_ua'])) !!}
                </div>
            </div>
        </div>
    </div>
</div>
