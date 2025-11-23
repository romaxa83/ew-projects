@extends('layouts.app')

@push('extendHeader')
    <link rel="stylesheet" media="screen, print" href="{{ mix('/css/dispatch.css') }}">
@endpush

@push('extendFooter')
    {{--    <script src="https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.11/lib/droppable.js"></script>--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.11/lib/draggable.bundle.js"></script>--}}
    <script src="{{ mix('/js/dispatch.js') }}"></script>
@endpush


@section('content')
    <div id="content-spinner" class="frame-wrap position-absolute w-100 h-100 opacity-50 d-none">
        <div class="w-100 d-flex justify-content-center align-items-center">
            <div class="spinner-border text-info position-absolute" style="top:50%;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    <div class="subheader">
        <h1 class="subheader-title">
            Dispatch
            @if($user->isPartner())
                <span
                    id="dispatch-title-spinner"
                    aria-hidden="true"
                    class="fs-xs spinner-border spinner-border-sm"
                ></span>
            @endif
        </h1>
    </div>
    <div class="d-flex">
        <div class="flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-clean" role="tablist-link-tracked">
                <li class="nav-item">
                    <a class="nav-link active"
                        data-toggle="tab-link-tracked"
                        href="#tab-schedule-trucks"
                        role="tab"
                        aria-selected="true"
                    >Trucks</a>
                </li>
                @if(!$user->isPartner())
                    <li class="nav-item">
                        <a class="nav-link"
                           data-toggle="tab-link-tracked"
                           href="#tab-schedule-crews"
                           role="tab"
                           aria-selected="false"
                        >Crews</a>
                    </li>
                @endif
                @if(!$user->isPartner())
                    <li class="nav-item">
                        <a class="nav-link"
                           data-toggle="tab-link-tracked"
                           data-trigger-global-event="dispatch:works:show-changelog-tab"
                           href="#tab-changelog"
                           role="tab"
                           aria-selected="false"
                        >Changelog</a>
                    </li>
                @endif
            </ul>
        </div>
        <dispatch-works-header ref="Dispatch"
                               init-date="{{ $date->toDateTimeString() }}"
        ></dispatch-works-header>
    </div>


    <div class="tab-content mt-md-3 mt-6">

        <div class="tab-pane fade active show" role="tabpanel" id="tab-schedule-trucks" aria-labelledby="tab-schedule-trucks">
            <dispatch-works-trucks-top-panel can-manage="{{ !$user->isPartner() }}"></dispatch-works-trucks-top-panel>
            <div class="row">
                <div class="col-lg-12">
                    @include('layouts.dispatch.tabs.schedule.gannt_trucks', [
                        'canManage' => !$user->isPartner(),
                    ])
                </div>
            </div>
        </div>
        @if(!$user->isPartner())
            <div class="tab-pane fade" role="tabpanel" id="tab-schedule-crews" aria-labelledby="tab-schedule-crews">
                <div class="row">
                    <div class="col-lg-12">
                        <dispatch-works-crews-top-panel can-manage="1"></dispatch-works-crews-top-panel>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        @include('layouts.dispatch.tabs.schedule.gannt_crews')
                    </div>
                </div>
            </div>
        @endif
        @if(!$user->isPartner())
            <div class="tab-pane fade" role="tabpanel" id="tab-changelog" aria-labelledby="tab-changelog">
                <div class="row">
                    <div class="col-lg-12">
                        <dispatch-works-changelog init-date="{{ $date->toDateTimeString() }}"></dispatch-works-changelog>
                    </div>
                </div>
            </div>
        @endif
    </div>
{{--    @include('layouts.dispatch.tabs.schedule.modals')--}}
@endsection
