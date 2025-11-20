<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
            </div>
            <div class="card-body form-horizontal">
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('name', __('cms-core::admin.administrators.Name'), ['class' => 'col-sm-3 required']) !!}
                        <div class="col-sm-9">
                            {!! Form::text('name', null, ['placeholder' => __('cms-core::admin.administrators.Name')]) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('email', __('cms-core::admin.administrators.E-mail'), ['class' => 'col-sm-3 required']) !!}
                        <div class="col-sm-9">
                            {!! Form::email('email', null, ['placeholder' => __('cms-core::admin.administrators.E-mail'), 'autocomplete' => 'off']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.profile.Password')</strong></h5>
            </div>
            <div class="card-body form-horizontal">
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('password', __('cms-core::admin.profile.Password'), ['class' => 'col-sm-3']) !!}
                        <div class="col-sm-9">
                            {!! Form::password('password', ['autocomplete' => 'off']) !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        {!! Form::label('password_confirmation', __('cms-core::admin.profile.Confirm password'), ['class' => 'col-sm-3']) !!}
                        <div class="col-sm-9">
                            {!! Form::password('password_confirmation', ['autocomplete' => 'off']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @foreach($renderLeftForm as $eventContent)
            {!! $eventContent !!}
        @endforeach
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.administrators.Additionally')</strong></h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('active', __('cms-core::admin.administrators.Status')) !!}
                    {!! Form::status('active') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('image', __('cms-core::admin.administrators.Image')) !!}
                    {!! Form::imageUploader('image', $obj, route('admin.administrators.delete-image', $obj->id)) !!}
                </div>
                @foreach($eventFields as $eventContent)
                    {!! $eventContent !!}
                @endforeach
            </div>
        </div>
        @if(! $obj->exists || ! $obj->isSuperAdmin())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="py-2"><strong>@lang('cms-core::admin.administrators.Roles list')</strong></h5>
                </div>
                <div class="card-body form-horizontal">
                    @foreach($roles as $role)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="ROLES[]" class="custom-control-input" id="role-{{ $role->id }}"
                                   {{ in_array($role->id, $selectedRoles) ? 'checked' : '' }}
                                   value="{{ $role->id }}">
                            <label class="custom-control-label" for="role-{{ $role->id }}" title="{{ $role->description ?: $role->display_name }}">
                                &nbsp;{{ $role->display_name ?: $role->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
