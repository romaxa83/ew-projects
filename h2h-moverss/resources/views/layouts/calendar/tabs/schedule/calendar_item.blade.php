<div class="d-flex">
    <div>
        <h3>
            {!! $day['is_today'] ? '<span class="badge badge-info">':'' !!}

            <a class="fs-xxl {!! (isset($day['stat']) && $day['stat']['display_info']) ? 'text-success cursor-pointer day-detail':'' !!}"
               href="{{ route('dispatch.schedule', ['start_date' => $day['dt']->format('Y-m-d') ]) }}#tab-schedule-trucks">
                {{ $day['dt']->format('d') }} <span class="fs-xl">{{$day['dt']->format('M')}}</span>
            </a>

            {!! $day['is_today'] ? '</span>':'' !!}
        </h3>
    </div>
    @if($current_day->lte($day['dt']))
        <div class="ml-auto">
            <button class="btn btn-info btn-sm btn-icon rounded-circle waves-effect waves-themed"
                    title="Create Order"
                    onclick="window.VueApp.$refs.Calendar.createOrder('{{ $day['dt']->format('Y-m-d') }}')">
                <i class="fal fa-plus"></i>
            </button>
        </div>
    @endif
</div>
{{--@dd($current_day, $day['dt'], $day['stat'])--}}
@if(isset($day['stat']))
    <div class="text-muted text-right d-flex" title="Booked Services">
        <div class="flex-grow-1">
            {{ $day['stat']['booked_works'] }}
        </div>
        <div class="width-1">
            <i class="fal fa-truck-moving fs-nano"></i>
        </div>
    </div>
    <div class="text-muted text-right d-flex" title="Assigned Trucks">
        <div class="flex-grow-1">
            {{ $day['stat']['trucks_assigned'] }}/{{ $day['stat']['trucks_total'] }}
        </div>
        <div class="width-1">
            <i class="fas fa-truck fs-nano"></i>
        </div>
    </div>
    <div class="text-muted text-right d-flex" title="Assigned Employees">
        <div class="flex-grow-1">
            {{ $day['stat']['employees_assigned'] }} /{{ $day['stat']['employees_total'] }}
        </div>
        <div class="width-1">
            <i class="fas fa-user fs-nano"></i>
        </div>
    </div>

    @if($current_day->startOfDay()->gt($day['dt']))
{{--        @dd($current_day->startOfDay(), $day['dt'])--}}
        <div class="text-muted text-left d-flex" title="Not closed orders">
            <div class="flex-grow-1">
                {{ $day['stat']['not_close_order'] }}
            </div>
        </div>
    @endif
@endif
