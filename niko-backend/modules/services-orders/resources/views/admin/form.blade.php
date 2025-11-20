<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('name', __('cms-services-orders::admin.Name')) !!}
                    {!! Form::text('name') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('phone', __('cms-services-orders::admin.Phone')) !!}
                    {!! Form::text('phone') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('email', __('cms-services-orders::admin.E-mail')) !!}
                    {!! Form::email('email') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('city', __('cms-services-orders::admin.City')) !!}
                    {!! Form::text('city') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('message', __('cms-services-orders::admin.Message')) !!}
                    {!! Form::textarea('message') !!}
                </div>
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
                    {!! Form::label('read', __('cms-services-orders::admin.Status')) !!}
                    {!! Form::status('read') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('service_id', __('cms-services-orders::admin.Service')) !!}
                    {!! Form::select('service_id', $services, null, ['class' => 'js-select2']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
