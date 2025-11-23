@php
    /** @var $obj \WezomCms\Users\Models\Inviter */
@endphp

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('surname', __('cms-users::admin.Surname')) !!}
                            {!! Form::text(
                                'surname',
                                old('surname', $obj->surname),
                                [ 'disabled' => true ])
                            !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('name', __('cms-users::admin.Name')) !!}
                            {!! Form::text(
                                'name',
                                old('name', $obj->name),
                                [ 'disabled' => true ])
                            !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('patronymic', __('cms-users::admin.Patronymic')) !!}
                            {!! Form::text(
                                'patronymic',
                                old('patronymic', $obj->patronymic),
                                [ 'disabled' => true ])
                            !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('email', __('cms-users::admin.E-mail')) !!}
                            {!! Form::email(
                                'email',
                                old('email', $obj->email),
                                [ 'disabled' => true ])
                            !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('phone', __('cms-users::admin.Phone')) !!}
                            {!! Form::text(
                                'phone',
                                old('phone', $obj->phone),
                                [ 'disabled' => true ])
                            !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-users::admin.Bonuses')</strong></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('bonus', __('cms-users::admin.referrals.Bonus sum')) !!}
                            {!! Form::number('bonus', old('bonus', $obj->bonus)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2"><strong>@lang('cms-users::admin.Referrals')</strong></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>@lang('cms-users::admin.Full name')</th>
                                <th>@lang('cms-users::admin.referrals.Accrued bonuses sum')</th>
                                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
                            </tr>
                        </thead>
                        <tbody id="referral-list">
                            @foreach($referrals as $referral)
                                <tr data-referral-id="{{ $referral->id }}">
                                    <td>@editResource($referral, $referral->full_name)</td>
                                    <td>{{ $referral->getReferralBonusSum() }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger js-delete-referral">
                                            @lang('cms-core::admin.layout.Delete')
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
           var $referralList = $('#referral-list');

           $referralList.on('click', '.js-delete-referral', function () {
               var $this = $(this);
               var listItem = $this.closest('tr');
               var referralId = listItem.data('referral-id');

               var url = route('admin.referrals.detach', { referral: referralId });

               $.get(url, function (data) {
                   if (data.success) {
                       listItem.remove();
                   }
               });
           });
        });
    </script>
@endpush
