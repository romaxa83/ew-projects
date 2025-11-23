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
    <div>
        @yield('page-title-buttons', '')
    </div>
{{--    <div>--}}
{{--        <form--}}
{{--            method="POST"--}}
{{--            action="http://192.168.175.1/wezom/products/import"--}}
{{--            accept-charset="UTF-8"--}}
{{--            id="form"--}}
{{--            enctype="multipart/form-data"--}}
{{--        >--}}

{{--            <input class="form-control-file" name="import" type="file" id="import">--}}
{{--            <div class="mt-3 mb-3">--}}
{{--                <div class="js-form-controls text-right">--}}
{{--                    <button class="btn btn-sm btn-success"--}}
{{--                            type="submit"--}}
{{--                    >--}}
{{--                        Сохранить--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
</div>
