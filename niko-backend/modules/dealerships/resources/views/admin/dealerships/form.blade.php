@php
    /**
     * @var $viewPath string
     */
    $tabs = [
        __('cms-core::admin.layout.Main data') => $viewPath . '.tabs.main',
        __('cms-core::admin.tabs.gallery') => $viewPath . '.tabs.gallery',
        __('cms-core::admin.tabs.schedule salon') => $viewPath . '.tabs.schedule-salon',
        __('cms-core::admin.tabs.schedule service') => $viewPath . '.tabs.schedule-service',
    ];
@endphp
<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @tabs($tabs)
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
                    {!! Form::number('lat', old('lat', $obj->location ? $obj->location->getLat() : null)) //['step' => 0.01]!!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('lon'), __('cms-regions::admin.longitude')) !!}
                    {!! Form::number('lon', old('lon', $obj->location ? $obj->location->getLng() : null ))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('email', __('cms-dealerships::admin.Email')) !!}
                    {!! Form::email('email', old('email', $obj->email))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('site_link', __('cms-dealerships::admin.Site link')) !!}
                    {!! Form::text('site_link', old('site_link', $obj->site_link))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('city_id', __('cms-regions::admin.City')) !!}
                    <div class="input-group">
                        {!! Form::select('city_id', $cities, old('city_id', $selectedCity), ['class' => 'js-select2']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('brand_id', __('cms-cars::admin.Brand')) !!}
                    <div class="input-group">
                        {!! Form::select('brand_id', $brands, old('brand_id', $selectedBrand), ['class' => 'js-select2']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
