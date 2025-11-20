<div class="page-titles px-3 py-2 mb-0 d-flex justify-content-between">
    <div class="d-flex">
        @if($heading)
            <h3 class="text-primary" @if($breadcrumbs) title="{{ $breadcrumbs }}"@endif>{{ $heading }}</h3>
        @endif
        @if(trim($__env->yieldContent('filter')))
            <button class="btn btn-sm shadow-none"
                    title="@lang('cms-core::admin.filter.Filter')"
                    data-target="#filter-body"
                    style="font-size: 19px; padding: 0 .5rem"
                    aria-expanded="{{ ($expanded ?? false) ? 'true' : 'false' }}" data-toggle="collapse" aria-controls="filter-block"
            ><i class="fa fa-filter"></i></button>
        @endif
    </div>
    <div>@yield('page-title-buttons', '')</div>
</div>
