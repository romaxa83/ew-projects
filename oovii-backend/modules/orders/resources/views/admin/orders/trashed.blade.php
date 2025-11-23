@extends('cms-core::admin.crud.index')

@php
    /**
     * @var $result \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator|\WezomCms\Orders\Models\Order[]
     */
@endphp

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName, true)</th>
                <th width="1%">#</th>
                <th>@lang('cms-orders::admin.orders.Full name')</th>
                <th>@lang('cms-orders::admin.orders.Phone')</th>
                <th>@lang('cms-orders::admin.orders.Email')</th>
                <th>@lang('cms-orders::admin.orders.Count items/Total cost')</th>
                <th>@lang('cms-orders::admin.orders.Status')</th>
                <th>@lang('cms-orders::admin.orders.Date')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>
                        @if($obj->client)
                            @if($obj->user && Gate::allows('users.edit', $obj->user))
                                <a href="{{ route('admin.users.edit', $obj->user->id) }}"
                                   target="_blank">{{ $obj->client->full_name }}</a>
                            @else
                                {{ $obj->client->full_name }}
                            @endif
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        @if($obj->client && $obj->client->phone)
                            <a href="tel:{{ preg_replace('/[^\d\+]/', '', $obj->client->phone) }}">{{ $obj->client->phone }}</a>
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        @if($obj->client && $obj->client->email)
                            <a href="mailto:{{ $obj->client->email }}">{{ $obj->client->email }}</a>
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>{{ $obj->quantity . ' / ' . number_format($obj->whole_purchase_price, 2, '.', ' ') . ' ' . money()->adminCurrencySymbol() }}</td>
                    <td>
                            <span data-toggle="tooltip" data-html="true"
                                  title="{!! $obj->statusHistory->map(function($status) {return $status->name . ' ' . $status->pivot->created_at;})->implode('<br>') !!}"
                            >{{ $obj->status->name ?? '' }}</span>
                    </td>
                    <td>
                        @if($obj->created_at)
                            {{ $obj->created_at }}
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @showResource($obj, false)
                            @restoreResource($obj)
                            @deleteResource($obj)
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
