<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                @langTabs
                    <div class="form-group">
                        {!! Form::label($locale . '[name]', __('cms-catalog::admin.brands.Name')) !!}
                        {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name), ['class' => ($loop->first ? 'slug-source' : '')]) !!}
                    </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published') !!}
                </div>
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('slug', __('cms-core::admin.layout.Slug')) !!}--}}
{{--                    {!! Form::slugInput('slug', old('slug', $obj->slug), ['source' => 'input.slug-source']) !!}--}}
{{--                </div>--}}
                <div class="form-group">
                    {!! Form::label('image', __('cms-catalog::admin.brands.Image')) !!}
                    {!! Form::imageUploader('image', $obj) !!}
                </div>
            </div>
        </div>
    </div>
</div>
