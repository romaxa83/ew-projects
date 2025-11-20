<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @langTabs
                    <div class="form-group">
                        {!! Form::label($locale . '[name]', __('cms-services::admin.Name')) !!}
                        {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label($locale . '[text]', __('cms-services::admin.Text')) !!}
                        {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text), ['data-lang' => $locale]) !!}
                    </div>

                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published') !!}
                </div>
                @if(config('cms.services.services.use_groups'))
                    <div class="form-group">
                        {!! Form::label('service_group_id', __('cms-services::admin.Group')) !!}
                        {!! Form::select('service_group_id', $groups, null, ['class' => 'js-select2']) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
