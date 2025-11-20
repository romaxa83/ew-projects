@extends('cms-core::admin.crud.index', ['btnCreateHide' => true])

@php
    /**
     * @var $result \WezomCms\Supports\Models\Support[]
     */
@endphp

@section('content')

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th>@lang('cms-supports::admin.Name')</th>
                <th>@lang('cms-supports::admin.Email')</th>
                <th>@lang('cms-supports::admin.Text')</th>
                <th>@lang('cms-supports::admin.Created')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody data-params="{{ json_encode(['model' => encrypt($model)]) }}">
            @foreach($result as $obj)
                <tr data-id="{{ $obj->id }}">
                    <td>@massCheck($obj)</td>
                    <td>{{$obj->name}}</td>
                    <td>
                        @if($obj->email)
                            <a href="mailto:{{ $obj->email }}">{{ $obj->email }}</a>
                        @else
                            <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
                        @endif
                    </td>
                    <td>@editResource(['obj' => $obj, 'text' => str_limit(strip_tags($obj->text), 50) ?: null])</td>
                    <td>{{ $obj->created_at->format('d.m.Y') }}</td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            @smallStatus(['obj' => $obj, 'field' => 'read'])
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

