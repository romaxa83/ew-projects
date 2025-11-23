<div class="row">
    <div class="col-lg-6">
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
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('name', __('cms-providers::admin.provider.Name')) !!}
                            {!! Form::text('name') !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('company', __('cms-provider::admin.company.Company')) !!}
                            {!! Form::text('company') !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('region_code', __('cms-provider::admin.Region')) !!}
                            {!! Form::select(
                                'region_code',
                                $regions,
                                null,
                                [ 'id' => 'sdek-region-select', 'class' => 'js-select2' ]
                            ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('city_code', __('cms-provider::admin.City')) !!}
                            {!! Form::select(
                                'city_code',
                                $cities,
                                $citiesList,
                                [
                                    'id' => 'sdek-city-select',
                                    'class' => 'js-ajax-select2',
                                    'data-url' => route('admin.sdek.search-cities', [
                                        'region' => $obj->region_code ?? $regions->keys()->first(),
                                    ]),
//                                    'data-minimum-input-length' => 3
                                ]
                            ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('address', __('cms-provider::admin.company.Address')) !!}
                            {!! Form::text('address') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
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
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-users::admin.Additionally')</strong></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('active', __('cms-users::admin.Status')) !!}
                            {!! Form::status('active') !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('email_verified', __('cms-providers::admin.Email verified')) !!}
                            {!! Form::status('email_verified', old('email_verified', $obj->email_verified), true, __('cms-users::admin.Yes'), __('cms-users::admin.No'))  !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone_verified', __('cms-providers::admin.Phone verified')) !!}
                            {!! Form::status('phone_verified', old('phone_verified', $obj->phone_verified), true, __('cms-users::admin.Yes'), __('cms-users::admin.No'))  !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('phone', __('cms-users::admin.Phone')) !!}
                    {!! Form::text('phone') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('email', __('cms-users::admin.E-mail')) !!}
                    {!! Form::email('email') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('status', __('cms-providers::admin.Status')) !!}
                    <div class="input-group">
                        {!! Form::select('status', $statuses, old('status', $selectedStatus), ['class' => 'js-select2']) !!}
                    </div>
                </div>
                <div class="list-group mt-5">
                    @if (isset($obj->adminProfile) && $productsCount = $obj->products()->count())
                        <a href="{{ route('admin.products.index', [ 'provider_id' => $obj->admin_id ]) }}" target="_blank"
                           class="list-group-item list-group-item-action font-weight-bold lead">
                            @lang('cms-providers::admin.provider.Provider products')
                            <span class="label label-rounded label-primary">{{ $productsCount }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
