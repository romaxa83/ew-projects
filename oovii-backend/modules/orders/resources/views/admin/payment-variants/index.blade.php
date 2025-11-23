@extends('cms-core::admin.crud.index')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="sortable-column"></th>
                <th width="1%">@massControl($routeName)</th>
                <th width="1%">@lang('cms-orders::admin.delivery-and-payment.Image')</th>
                <th>@lang('cms-orders::admin.delivery-and-payment.Name')</th>
                <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
            </tr>
            </thead>
            <tbody class="js-sortable"
                   data-params="{{ json_encode(['model' => encrypt($model), 'page' => $result->currentPage(), 'limit' => $result->perPage()]) }}">
            @foreach($result as $obj)
                <tr data-id="{{ $obj->id }}">
                    <td>
                        <div class="js-sortable-handle sortable-handle">
                            <i class="fa fa-arrows"></i>
                        </div>
                    </td>
                    <td>@massCheck($obj)</td>
                    <td>
                        @if($obj->imageExists())
                            <a href="{{ url($obj->getImageUrl()) }}" data-fancybox>
                                <img src="{{ url($obj->getImageUrl()) }}" alt="{{ $obj->name }}" height="50">
                            </a>
                        @else
                            <img src="{{ url($obj->getImageUrl()) }}" alt="{{ $obj->name }}" height="50">
                        @endif
                    </td>
                    <td>@editResource($obj)</td>
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
