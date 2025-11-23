@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-product-reviews::admin.Name')</th>
                <th>@lang('cms-product-reviews::admin.E-mail')</th>
                <th>@lang('cms-product-reviews::admin.Answer for')</th>
                <th>@lang('cms-product-reviews::admin.Product')</th>
                <th>@lang('cms-product-reviews::admin.Date')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>
                        @if($obj->user)
                            <a href="{{ route('admin.users.edit', $obj->user->id) }}"
                               title="{{ $obj->user->full_name }}"
                               target="_blank">{{ $obj->user->full_name }}
                            </a>
                        @else
                            @editResource($obj)
                        @endif
                    </td>
                    <td>
                        @if(isset($obj->user->email))
                            <a href="mailto:{{ $obj->user->email }}">{{ $obj->user->email }}</a>
                        @elseif($obj->email)
                            <a href="mailto:{{ $obj->email }}">{{ $obj->email }}</a>
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        @if($obj->parent_id)
                            @editResource($obj->parent, $obj->parent->getReviewFullName())
                        @else
                            ---
                        @endif
                    </td>
                    <td>
                        @if($obj->product)
                            <a href="{{ route('admin.products.edit', $obj->product->id) }}"
                               title="{{ $obj->product->name }}"
                               target="_blank">{{ $obj->product->name }}
                            </a>
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        @if($obj->created_at)
                            {{ $obj->created_at->format('Y-m-d') }}
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus($obj)
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
