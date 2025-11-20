<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('name', __('cms-core::admin.roles.Name'), ['class' => 'required']) !!}
                    {!! Form::text('name', null, ['placeholder' => __('cms-core::admin.roles.Name')]) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.roles.Permissions list')</strong></h5>
            </div>
            <div class="card-body">
                <div class="check-wrapper">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="js-check-child custom-control-input" id="check-all">
                        <label class="custom-control-label" for="check-all">@lang('cms-core::admin.roles.Check all')</label>
                    </div>
                    <hr>
                    @foreach($permissions as $key => $group)
                        <div>
                            @if(($group['checkboxes'] ?? false) && count($group['checkboxes']) > 1)
                                <div class="check-wrapper">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="js-check-child custom-control-input" value="" id="check-perm-{{ $key }}">
                                        <label class="custom-control-label" for="check-perm-{{ $key }}">{{ $group['name'] }}</label>
                                    </div>

                                    <div class="ml-4">
                                        @foreach($group['checkboxes'] as $permission => $name)
                                            <div class="custom-control custom-checkbox mb-1">
                                                <input type="checkbox" name="permissions[]" class="custom-control-input" id="permission-{{ $permission }}"
                                                        {{ in_array($permission, $selectedPermissions) ? 'checked' : '' }}
                                                        value="{{ $permission }}">
                                                <label class="custom-control-label" for="permission-{{ $permission }}">{{ $name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @php
                                    if (($group['checkboxes'] ?? false) && count($group['checkboxes']) == 1) {
                                        $key = key($group['checkboxes']);
                                    }
                                @endphp
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" name="permissions[]" class="custom-control-input" id="permission-{{ $key }}"
                                           {{ in_array($key, $selectedPermissions) ? 'checked' : '' }}
                                           value="{{ $key }}">
                                    <label class="custom-control-label" for="permission-{{ $key }}">{{ $group['name'] }}</label>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
