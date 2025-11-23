@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-core::admin.layout.ID')</th>
                <th>@lang('cms-providers::admin.provider.Name')</th>
                <th>@lang('cms-users::admin.Phone')</th>
                <th>@lang('cms-users::admin.E-mail')</th>
                <th>@lang('cms-providers::admin.company.Name')</th>
                <th>@lang('cms-providers::admin.provider.Products count')</th>
                <th>@lang('cms-providers::admin.Status')</th>
                <th>@lang('cms-core::admin.layout.Created at')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>{{ $obj->name }}</td>
                    <td>
                        @if($obj->phone)
                            <a href="tel:{{ preg_replace('/[^\d\+]/', '', $obj->phone) }}">
                                <span style="color:{{$obj->phone_verified ? '#5B6279': '#e64854'}}">
                                    {{ $obj->phone }}
                                </span>
                            </a>
                        @else
                            <span class="text text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        @if($obj->email)
                            <span style="color:{{$obj->email_verified ? '#5B6279': '#e64854'}}">
                                {{ $obj->email }}
                            </span>
                        @else
                            <span class="text text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>{{ $obj->company}}</td>
                    <td>{{ $obj->products_count }}</td>
                    <td>
                        {!! $obj->statusRender() !!}
                    </td>
                    <td>
                        {!! $obj->created_at->format(config('cms.core.time.format.created_at.admin_table')) !!}
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @if(isset($obj->adminProfile))
                                <a href="{{ route('admin.products.index', ['provider_id' => $obj->adminProfile->id]) }}"
                                   class="btn btn-info"
                                   title="@lang('cms-providers::admin.product show')">
                                    <i class="fa fa-eye"></i>
                                </a>
                            @endif
                            @smallStatus(['obj' => $obj, 'field' => 'active'])
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
