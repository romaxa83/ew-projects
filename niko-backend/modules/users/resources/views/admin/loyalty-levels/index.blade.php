@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-users::admin.level')</th>
                <th>@lang('cms-users::admin.loyalty count auto')</th>
                <th>@lang('cms-users::admin.loyalty sum service')</th>
                <th>@lang('cms-users::admin.loyalty discount_sto')</th>
                <th>@lang('cms-users::admin.loyalty discount_spares')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ \WezomCms\Users\Types\LoyaltyLevel::getName($obj->level) }}</td>
                    <td>{{ $obj->count_auto }}</td>
                    <td>{{ $obj->getSumServices() }}</td>
                    <td>{{ $obj->getDiscountSto() }}</td>
                    <td>{{ $obj->getDiscountSpares() }}</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @editResource($obj, false)
                            @deleteResource($obj)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
