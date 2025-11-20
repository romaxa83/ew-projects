<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @langTabs
                <div class="form-group">
                    {!! Form::label($locale . '[name]', __('cms-core::admin.layout.Name')) !!}
                    {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
                </div>
                @endLangTabs
            </div>
        </div>
    </div>
</div>

