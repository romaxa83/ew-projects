@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="1%">@massControl($routeName)</th>
                <th width="1%">ID</th>
                <th width="1%">@lang('cms-catalog::admin.collection.image')</th>
                <th>@lang('cms-catalog::admin.collection.name')</th>
                <th>@lang('cms-catalog::admin.collection.type.title')</th>
                <th>@lang('cms-catalog::admin.collection.qty-product')</th>
{{--                <th>@lang('cms-catalog::admin.collection.category.one')</th>--}}
                <th>@lang('cms-core::admin.moderator.moderator')</th>
                <th>@lang('cms-catalog::admin.collection.start_at')</th>
                <th>@lang('cms-catalog::admin.collection.end_at')</th>
                <th>@lang('cms-core::admin.layout.Created at')</th>
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
                    <td>{{$obj->type_pretty}}</td>
                    <td>{{$obj->products_count}}</td>
{{--                    <td>--}}
{{--                        @if($obj->category)--}}
{{--                            {{ $obj->category->name }}--}}
{{--                        @endif--}}
{{--                    </td>--}}
                    <td>{{ $obj->moderator->name ?? null }}</td>
                    <td>{!! $obj->start_at != null ? $obj->start_at->format("Y-m-d H:i")  : null !!}</td>
                    <td>{!! $obj->end_at != null ? $obj->end_at->format("Y-m-d H:i") : null !!}</td>
                    <td>
                        {!! $obj->created_at->format(config('cms.core.time.format.created_at.admin_table')) !!}
                    </td>
                    <td>
                        <div class="btn-group list-control-buttons" role="group">
                            <a href="{{ route('admin.products.index', ['collection_id' => $obj->id]) }}"
                               class="btn btn-info"
                               title="@lang('cms-catalog::admin.collection.show_product')">
                                <i class="fa fa-eye"></i>
                            </a>
{{--                            <div class="btn-group js-status-switcher"--}}
{{--                                 data-type="small" data-text-on="test"--}}
{{--                                 data-text-off="test off"--}}
{{--                                 data-model="{{ encrypt(get_class($obj)) }}"--}}
{{--                                 role="group">--}}
{{--                                <button type="button" class="btn btn-{{ $obj->isPublished() ? 'success' : 'outline-secondary' }}"--}}
{{--                                        data-id="{{ $obj->id }}"--}}
{{--                                        data-status="{{ $obj->isPublished() }}"--}}
{{--                                        data-field="published"--}}
{{--                                        title="{{ $obj->isPublished() ? 'text on' : 'text off' }}" data-toggle="tooltip" data-placement="top"--}}
{{--                                ><i class="fa {{ $obj->isPublished() ? 'fa-check-square-o' : 'fa-square-o' }}"></i>--}}
{{--                                </button>--}}
{{--                            </div>--}}
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

