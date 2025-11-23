@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th width="1%">ID</th>
                <th width="1%">@lang('cms-catalog::admin.brands.Image')</th>
                <th>@lang('cms-catalog::admin.brands.Name')</th>
{{--                <th>@lang('cms-core::admin.layout.Slug')</th>--}}
                <th width="150px">@lang('cms-catalog::admin.brands.Position')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $obj)
                <tr>
                    <td>@massCheck($obj)</td>
                    <td>{{ $obj->id }}</td>
                    <td>
                        <a href="{{ $obj->getImageUrl() }}" data-fancybox>
                            <img src="{{ $obj->getImageUrl() }}" alt="{{ $obj->name }}"
                                 style="max-height:50px;">
                        </a>
                    </td>
                    <td>@editResource($obj)</td>
{{--                    <td>{{ $obj->slug }}</td>--}}
                    <td>
                        @can('brands.edit', $obj)
                            {!! Form::open(['route' => ['admin.brands.set-sort', $obj->id], 'method' => 'post', 'class' => 'js-ajax-form']) !!}
                            <div class="input-group">
                                {!! Form::number('sort', $obj->sort, ['required' => 'required']) !!}
                                <div class="input-group-append">
                                    {!! Form::button('<i class="fa fa-floppy-o"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-secondary']) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        @else
                            {{ $obj->sort }}
                        @endcan
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
