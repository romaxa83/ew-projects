<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item"><a href="javascript:void(0);">{{ config('app.name') }}</a></li>
    @foreach($breadcrumbs as $v)
        <li class="breadcrumb-item{!! $loop->last ? ' active':'' !!}">{!! !empty($v['href']) ? "<a href=\"{$v['href']}\">{$v['title']}</a>" : $v['title'] !!}</li>
    @endforeach
    {{--            <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date">Tuesday, April 28, 2020</span>--}}
    </li>
</ol>
