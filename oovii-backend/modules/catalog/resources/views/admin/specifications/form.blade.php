<div class="row">
    <div class="col-lg-{{ $hasAnImage ? 7 : 12 }}">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                            {!! Form::status('published') !!}
                        </div>
                    </div>
{{--                    <div class="col-lg-3">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('multiple', __('cms-catalog::admin.specifications.Multiple')) !!}--}}
{{--                            {!! Form::status('multiple', null, false) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
                @langTabs
                    <div class="form-group">
                        {!! Form::label($locale . '[name]', __('cms-catalog::admin.specifications.Name')) !!}
                        {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name), ['class' => ($loop->first ? 'slug-source' : '')]) !!}
                    </div>
                @endLangTabs
                <div class="form-group">
                    {!! Form::label('slug', __('cms-core::admin.layout.Slug')) !!}
                    {!! Form::slugInput('slug', old('slug', $obj->slug), ['source' => 'input.slug-source']) !!}
                </div>
            </div>
        </div>
    </div>
    @if($hasAnImage)
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('image', __('cms-catalog::admin.specifications.Image')) !!}
                        {!! Form::imageUploader('image', $obj) !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if($obj->exists)
    <div class="card">
        <div class="card-header">
            <h6 class="pt-1 pb-1"><strong>@lang('cms-catalog::admin.specifications.Values list')</strong></h6>
        </div>
        <div class="card-body vue-specification">
            <specification-values resource-url="admin.spec-values"
                                  :specification-id="{{ $obj->id }}"
                                  ref="specifications"
                                  :locales="{{ json_encode(app('locales')) }}"
                                  :is-color="{{ $obj->isColor() ? 'true' : 'false' }}">
            </specification-values>
        </div>
    </div>
@endif
