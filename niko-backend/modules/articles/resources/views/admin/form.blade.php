<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @langTabs
                    <div class="form-group">
                        {!! Form::label(str_slug($locale . '[published]'), __('cms-core::admin.layout.Published')) !!}
                        {!! Form::status($locale . '[published]', old($locale . '.published', $obj->exists ? $obj->translateOrNew($locale)->published : true))  !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[name]', __('cms-articles::admin.Name')) !!}
                        {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[slug]', __('cms-core::admin.layout.Slug')) !!}
                        {!! Form::slugInput($locale . '[slug]', old($locale . '.slug', $obj->translateOrNew($locale)->slug), ['source' => 'input[name="' . $locale . '[name]"']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[text]', __('cms-articles::admin.Text')) !!}
                        {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text), ['class' => 'js-wysiwyg', 'data-lang' => $locale]) !!}
                    </div>

                    @include('cms-core::admin.partials.form-meta-inputs', compact('obj', 'locale'))
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">
                <h4>@lang('cms-core::admin.layout.Main data')</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('published_at', __('cms-articles::admin.Published at')) !!}
                    {!! Form::text('published_at', old('published_at', $obj->published_at ? $obj->published_at->format('d.m.Y') : null), ['class' => 'js-datepicker', 'placeholder' => __('cms-news::admin.Published at')]) !!}
                </div>
                @if(config('cms.articles.articles.use_groups'))
                    <div class="form-group">
                        {!! Form::label('article_group_id', __('cms-articles::admin.Group')) !!}
                        {!! Form::select('article_group_id', $groups, null, ['class' => 'js-select2']) !!}
                    </div>
                @endif
                <div class="form-group">
                    {!! Form::label('image', __('cms-articles::admin.Image')) !!}
                    {!! Form::imageUploader('image', $obj, route($routeName . '.delete-image', $obj->id)) !!}
                </div>
            </div>
        </div>
    </div>
</div>
