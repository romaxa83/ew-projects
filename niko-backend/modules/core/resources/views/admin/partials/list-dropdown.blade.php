@php

    $route = $route ?? '';
    $gate = str_replace('admin.', '', $route);
    $name = $name ?? 'name';

    $first = $list->first();
@endphp
@if($list && $list->isNotEmpty())
    <div class="btn-group">
        @if($route && Gate::allows($gate, $first))
            <a href="{{ route($route, $first->id) }}" class="btn btn-sm btn-outline-secondary"
               target="_blank">{{ $first->{$name} }}</a>
        @else
            <button type="button" class="btn btn-sm btn-outline-secondary">{{ $first->{$name} }}</button>
        @endcan
        @if($list->count() > 1)
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
            <div class="dropdown-menu">
                @foreach($list->splice(1) as $item)
                    @if($route && Gate::allows($gate, $item))
                        <a class="dropdown-item" href="{{ route($route, $item->id) }}"
                           target="_blank">{{ $item->{$name} }}</a>
                    @else
                        <span class="dropdown-item">{{ $item->{$name} }}</span>
                    @endcan
                @endforeach
            </div>
        @endif
    </div>
@else
    <span class="text-info">@lang('cms-core::admin.layout.Not set')</span>
@endif
