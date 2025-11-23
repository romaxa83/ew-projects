@extends('layouts.app')

@push('extendHeader')
    <link rel="stylesheet" media="screen, print" href="{{ mix('/css/dispatch.css') }}">
@endpush

{{--@push('extendFooter')--}}
{{--        <script src="https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.11/lib/droppable.js"></script>--}}
{{--    <script src="{{ mix('/js/dispatch.js') }}"></script>--}}
{{--@endpush--}}


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
            Calendar
            {{--                <small>--}}
            {{--                    Provide valuable, actionable feedback to your users with HTML5 form validation. Choose from the--}}
            {{--                    browser default validation feedback, or implement custom messages with our built-in classes and--}}
            {{--                    starter JavaScript.--}}
            {{--                </small>--}}
        </h1>
    </div>

    <div class="d-flex">
        <div class="flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-clean" role="tablist-link-tracked">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab-link-tracked" href="#tab-schedule-calendar" role="tab"
                       aria-selected="true">Calendar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab-link-tracked" href="#tab-calendar-week" role="tab"
                       aria-selected="false">7 days</a>
                </li>
            </ul>
        </div>
        <calendar-header ref="Calendar"
                         init-calendar-date="{{ $date->toDateTimeString() }}"
                         init-week-date="{{ $week_date->toDateTimeString() }}"
        ></calendar-header>
    </div>


    <div class="tab-content mt-md-3 mt-6">

        <div class="tab-pane fade active show" role="tabpanel" id="tab-schedule-calendar"
             aria-labelledby="tab-schedule-calendar">
            <div class="row">
                <div class="col-lg-12">
                    @include('layouts.calendar.tabs.schedule.calendar')
                </div>
            </div>
        </div>

        <div class="tab-pane fade" role="tabpanel" id="tab-calendar-week"
             aria-labelledby="tab-calendar-week">
            <div class="row">
                <div class="col-lg-12">
                    @include('layouts.calendar.tabs.schedule.calendar_week')
                </div>
            </div>
        </div>
    </div>
    {{--    @include('layouts.dispatch.tabs.schedule.modals')--}}
@endsection
