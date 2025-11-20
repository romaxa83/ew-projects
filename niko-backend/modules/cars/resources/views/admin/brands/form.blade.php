<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label(str_slug('published'), __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published') !!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('name'), __('cms-cars::admin.Name')) !!}
                    {!! Form::text('name', old('name', $obj->exists ? $obj->name : true))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('image', __('cms-cars::admin.Logo')) !!}
                    {!! Form::imageUploader('image', $obj, route($routeName . '.delete-image', $obj->id)) !!}
                </div>
            </div>
        </div>
    </div>
</div>
