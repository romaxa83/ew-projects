<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @langTabs
                    <div class="form-group">
                        {!! Form::label($locale . '[title]', __('cms-catalog::admin.catalog-seo-templates.Title')) !!}
                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="@lang('cms-catalog::admin.catalog-seo-templates.Category seo template meta-tags keys')"></i>
                        {!! Form::textarea($locale . '[title]', old($locale . '.title', $obj->translateOrNew($locale)->title), ['rows' => 3]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[h1]', __('cms-catalog::admin.catalog-seo-templates.H1')) !!}
                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="@lang('cms-catalog::admin.catalog-seo-templates.Category seo template meta-tags keys')"></i>
                        {!! Form::textarea($locale . '[h1]', old($locale . '.h1', $obj->translateOrNew($locale)->h1), ['rows' => 3]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[keywords]', __('cms-catalog::admin.catalog-seo-templates.Keywords')) !!}
                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="@lang('cms-catalog::admin.catalog-seo-templates.Category seo template meta-tags keys')"></i>
                        {!! Form::textarea($locale . '[keywords]', old($locale . '.keywords', $obj->translateOrNew($locale)->keywords), ['rows' => 3]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[description]', __('cms-catalog::admin.catalog-seo-templates.Description')) !!}
                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="@lang('cms-catalog::admin.catalog-seo-templates.Category seo template meta-tags keys')"></i>
                        {!! Form::textarea($locale . '[description]', old($locale . '.description', $obj->translateOrNew($locale)->description), ['rows' => 3]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[text]', __('cms-catalog::admin.catalog-seo-templates.Seo text')) !!}
                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="@lang('cms-catalog::admin.catalog-seo-templates.Category seo template meta-tags keys')"></i>
                        {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text), ['class' => 'js-wysiwyg'])  !!}
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
                <div class="form-group">
                    {!! Form::label('name', __('cms-catalog::admin.catalog-seo-templates.Name')) !!}
                    {!! Form::text('name') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('CATEGORIES[]', __('cms-catalog::admin.catalog-seo-templates.Categories')) !!}
                    <select name="CATEGORIES[]" id="CATEGORIES[]" multiple="multiple" class="form-control js-select2">
                        @foreach($categories as $key => $category)
                            <option value="{{ $key }}" {{ in_array($key, $selectedCategories) ? 'selected': null }}
                                    {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    {!! Form::label('PARAMETERS[]', __('cms-catalog::admin.catalog-seo-templates.Parameters')) !!}
                    {!! Form::select('PARAMETERS[]', $parameters, old('PARAMETERS', $selectedParameters), ['class' => 'js-select2', 'multiple' => 'multiple']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
