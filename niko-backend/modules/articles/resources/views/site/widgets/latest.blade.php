@php
    /**
     * @var $result \WezomCms\Articles\Models\Article[]
     * @var $linkForMore string
     */
@endphp

<div class="grid">
    @foreach($result as $obj)
        <div class="gcell">
            <div>
                <a href="{{ $obj->getFrontUrl() }}">
                    <img class="lozad js-import" src="{{ url('assets/images/empty.gif') }}"
                         data-lozad="{{ $obj->getImageUrl('main_page') }}" alt="{{ $obj->name }}">
                </a>
                <div>
                    <div>
                        <a href="{{ $obj->getFrontUrl() }}">{{ $obj->name }}</a>
                        <div>{!! str_limit(strip_tags($obj->text), 80) !!}</div>
                    </div>
                    <div>
                        <a href="{{ $obj->getFrontUrl() }}">@lang('cms-articles::site.Читати далі')</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
<div>
    <a href="{{ $linkForMore }}">@lang('cms-articles::site.Дізнатися більше')</a>
</div>
