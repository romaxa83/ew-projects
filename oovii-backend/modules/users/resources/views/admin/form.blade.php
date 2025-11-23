<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2">
                    <strong>@lang('cms-core::admin.layout.Main data')</strong>
                    @if ($obj->id)
                        <b>(ID - {{ $obj->id }})</b>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('surname', __('cms-users::admin.Surname')) !!}
                            {!! Form::text('surname') !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('name', __('cms-users::admin.Name')) !!}
                            {!! Form::text('name') !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    {{--<div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('patronymic', __('cms-users::admin.Patronymic')) !!}
                            {!! Form::text('patronymic') !!}
                        </div>
                    </div>--}}
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', __('cms-users::admin.E-mail')) !!}
                            {!! Form::email('email') !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('created_at', __('cms-users::admin.Registration date')) !!}
                            {!! Form::text('created_at', $obj->created_at, [ 'readonly' => true ]) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-users::admin.Password')</strong></h5>
{{--                @if($obj->id ?? false)--}}
{{--                    <div class="dib">--}}
{{--                        <a href="{{ route('admin.users.auth', $obj->id) }}" class="btn btn-sm btn-info" target="_blank"--}}
{{--                           data-toggle="tooltip"--}}
{{--                           title="@lang('cms-users::admin.log in as user')">@lang('cms-users::admin.Login')</a>--}}
{{--                    </div>--}}
{{--                @endif--}}
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('password', __('cms-users::admin.Password')) !!}
                    {!! Form::password('password', ['autocomplete' => 'new-password']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('password_confirmation', __('cms-users::admin.Confirm password')) !!}
                    {!! Form::password('password_confirmation', ['autocomplete' => 'new-password']) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-users::admin.Additionally')</strong></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('active', __('cms-users::admin.Status')) !!}
                            {!! Form::status('active') !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email_verified', __('cms-users::admin.Email verified')) !!}
                            {!! Form::status('email_verified', old('email_verified', $obj->email_verified_at ? true : false), true, __('cms-users::admin.Yes'), __('cms-users::admin.No'))  !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('phone', __('cms-users::admin.Phone')) !!}
                    {!! Form::text('phone')  !!}
                </div>
                <div class="list-group mt-5">
                    @if ($ordersCount = $obj->orders()->count())
                        <a href="{{ route('admin.orders.index', [ 'user_id' => $obj->id ]) }}" target="_blank"
                            class="list-group-item list-group-item-action font-weight-bold lead">
                            @lang('cms-users::admin.User orders')
                            <span class="label label-rounded label-primary">{{ $ordersCount }}</span>
                        </a>
                    @else
                        <span class="list-group-item list-group-item-action text-muted font-weight-bold lead">
                            @lang('cms-users::admin.User orders')
                            <span class="label label-rounded badge-secondary">{{ $ordersCount }}</span>
                        </span>
                    @endif
                    @if ($referralsCount = $obj->referrals()->count())
                        <a href="{{ route('admin.referrals.edit', [ 'referral' => $obj->id ]) }}" target="_blank"
                           class="list-group-item list-group-item-action font-weight-bold lead">
                            @lang('cms-users::admin.User referrals')
                            <span class="label label-rounded label-primary">{{ $referralsCount }}</span>
                        </a>
                    @else
                        <span class="list-group-item list-group-item-action text-muted font-weight-bold lead">
                            @lang('cms-users::admin.User referrals')
                            <span class="label label-rounded badge-secondary">{{ $referralsCount }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
