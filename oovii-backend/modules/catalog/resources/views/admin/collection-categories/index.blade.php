@extends('cms-core::admin.crud.index')

@section('content')
    @if(!empty($paginatedResult))
        <div class="m-3">
            <div class="dd js-nestable" data-settings-depth="{{ config('cms.catalog.categories.max_depth') }}"
                 data-control-list
                 data-params="{{ json_encode(['model' => encrypt($model)]) }}">
                @massControl($routeName)
                <ol class="dd-list">
                    @foreach($paginatedResult as $item)
                        @include($viewPath.'.index-list-item', ['result' => $result, 'item' => $item])
                    @endforeach
                </ol>
            </div>
        </div>
    @endif
@endsection
