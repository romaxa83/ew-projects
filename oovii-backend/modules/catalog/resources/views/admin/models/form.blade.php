<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @langTabs
                    <div class="form-group">
                        {!! Form::label($locale . '[name]', __('cms-catalog::admin.models.Name')) !!}
                        {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
                    </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('slug', __('cms-core::admin.layout.Slug')) !!}
                    {!! Form::slugInput('slug', old('slug', $obj->slug), ['source' => 'input.slug-source']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('brand_id', __('cms-catalog::admin.products.Brand')) !!}
                    {!! Form::select('brand_id', $selectedBrand ? [$selectedBrand->id => $selectedBrand->name] : [], old('brand_id', $obj->brand_id), ['class' => 'js-ajax-select2', 'data-url' => route('admin.brands.search')]) !!}
                </div>
            </div>
        </div>
    </div>
</div>
