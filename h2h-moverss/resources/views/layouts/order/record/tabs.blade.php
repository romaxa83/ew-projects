<div class="d-flex tabs-sticky">
    {{--    <div class="px-2 pt-1 " style="border-bottom: 1px solid rgba(0,0,0,.1)"></div>--}}
    <ul class="nav nav-tabs nav-tabs-clean bg-white" role="tablist" id="order-tabs" style="display: inline-flex; gap: 1px;">
        <li class="mb-0 pb-0 mt-0">
            <h1 class="mb-0 pl-1 mr-2" style="min-width: 175px"><span class="badge badge-secondary w-100">Order #{{ $record->id }}</span>
            </h1>
        </li>
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-overview" role="tab"
                                aria-selected="true">Overview</a></li>
        <li class="nav-item"><a class="nav-link" tab="#tab-overview" href="#client">Client</a></li>
        <li class="nav-item"><a class="nav-link" tab="#tab-overview" href="#works">Services</a></li>
        <li class="nav-item"><a class="nav-link" tab="#tab-overview" href="#waypoints">Waypoints</a></li>
        <li class="nav-item"><a class="nav-link" tab="#tab-overview" href="#estimate">Estimate
                <order-total-estimate-sum/>
            </a></li>
        {{--    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-activity" role="tab" aria-selected="false">Activity &amp; Communications</a>--}}
        {{--    </li>--}}
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-inventory" role="tab"
                                aria-selected="false">Inventory</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-inventory-new" role="tab"
                                aria-selected="false">Inventory&nbsp;<i class="red">NEW</i></a></li>
        {{--    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-notes" role="tab"--}}
        {{--                            aria-selected="false">Notes--}}
        {{--            @if($record->notes_count)--}}
        {{--                <sup><span class="badge badge-primary ml-2">{{ $record->notes_count }}</span></sup>--}}
        {{--            @endif--}}
        {{--        </a></li>--}}
        <li class="nav-item bg-white">
            <a class="nav-link" style="" data-toggle="tab" href="#tab-payments" role="tab"
               aria-selected="false">Payments</a></li>
        <li class="nav-item">
            <a class="nav-link" style="" data-toggle="tab" href="#tab-files" role="tab"
               aria-selected="false">Files {{--<span class="badge bg-danger-800 ml-2">4</span>--}}</a></li>
        <li class="nav-item bg-white">
            <a class="nav-link" style="" data-toggle="tab" href="#tab-comments" role="tab"
               aria-selected="false">Comments</a></li>
        <li class="flex-fill bg-white border-bottom" style="margin-bottom: -1px;"></li>
    </ul>
    <div class="flex-fill bg-white border-bottom">
    </div>
</div>


<div class="tab-content mt-md-6 mt-sm-6 mt-lg-6 mt-xl-3">

    <div class="tab-pane fade active show" role="tabpanel" id="tab-overview" aria-labelledby="tab-overview">
        <order-overview ref="overview"></order-overview>
    </div>
    {{--    <div class="tab-pane fade" role="tabpanel" id="tab-activity" aria-labelledby="tab-activity">--}}
    {{--        @include('layouts.order.record.tabs.activity')--}}
    {{--    </div>--}}
    <div class="tab-pane fade" role="tabpanel" id="tab-inventory" aria-labelledby="tab-inventory">
        @include('layouts.order.record.tabs.inventory')
    </div>
    <div class="tab-pane fade" role="tabpanel" id="tab-inventory-new" aria-labelledby="tab-inventory-new">
        @include('layouts.order.record.tabs.inventory-new')
    </div>
    {{--    <div class="tab-pane fade" role="tabpanel" id="tab-notes" aria-labelledby="tab-notes">--}}
    {{--        <order-notes></order-notes>--}}
    {{--    </div>--}}
    <div class="tab-pane fade" role="tabpanel" id="tab-payments" aria-labelledby="tab-payments">
        @include('layouts.order.record.tabs.payments')
    </div>
    <div class="tab-pane fade" role="tabpanel" id="tab-files" aria-labelledby="tab-files">
        <order-files></order-files>
    </div>
    <div class="tab-pane fade" role="tabpanel" id="tab-comments" aria-labelledby="tab-comments">
        @include('layouts.order.record.tabs.comments')
    </div>
</div>
