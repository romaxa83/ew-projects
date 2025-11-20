<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('name', __('cms-supports::admin.Name')) !!}
                    {!! Form::text('name', old('name', $obj->name))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('email', __('cms-supports::admin.Email')) !!}
                    {!! Form::text('email', old('name', $obj->email))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('text', __('cms-supports::admin.Text')) !!}
                    {!! Form::textarea('text', old('text', $obj->text))  !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('read', __('cms-callbacks::admin.Read')) !!}
                    {!! Form::status('read') !!}
                </div>
            </div>
        </div>
    </div>
</div>
