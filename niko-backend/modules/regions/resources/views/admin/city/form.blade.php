<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
            </div>
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
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label(str_slug('published'), __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published', old('published', $obj->exists ? $obj->published : true))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('lat'), __('cms-regions::admin.latitude')) !!}
                    {!! Form::text('lat', old('lat', $obj->lat))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('lon'), __('cms-regions::admin.longitude')) !!}
                    {!! Form::text('lon', old('lon', $obj->lon ))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('region_id', __('cms-regions::admin.Regions')) !!}
                    <div class="input-group">
                        {!! Form::select('region_id', $regions, old('region_id', $selectedRegion), ['class' => 'js-select2']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
