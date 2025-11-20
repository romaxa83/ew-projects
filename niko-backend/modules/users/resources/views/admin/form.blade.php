<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
            </div>
            <div class="card-body form-horizontal">
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('first_name', __('cms-users::admin.First name'), ['class' => 'col-sm-3']) !!}
                        <div class="col-sm-9">{!! Form::text('first_name') !!}</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('last_name', __('cms-users::admin.Last name'), ['class' => 'col-sm-3']) !!}
                        <div class="col-sm-9">{!! Form::text('last_name') !!}</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('email', __('cms-users::admin.E-mail'), ['class' => 'col-sm-3']) !!}
                        <div class="col-sm-9">{!! Form::email('email') !!}</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('phone', __('cms-users::admin.Phone'), ['class' => 'col-sm-3']) !!}
                        <div class="col-sm-9">{!! Form::email('phone') !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-users::admin.Additionally')</strong></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('phone_verified', __('cms-users::admin.Phone verified')) !!}
                            {!! Form::status('phone_verified', old('phone_verified', $obj->phone_verified ? true : false), true, __('cms-users::admin.Yes'), __('cms-users::admin.No'))  !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
