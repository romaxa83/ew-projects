<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('file', __('cms-imports::admin.file')) !!}
                    {!! Form::fileUploader('file', $obj) !!}
                </div>
            </div>
        </div>
    </div>
</div>
