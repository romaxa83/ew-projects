<div class="card">
    <div class="card-body p-0">
        <div class="calendar-wrapper">
            <div class="calendar">
                <div class="calendar__row calendar__row--header">
                    @foreach($days_names as $v)
                        <div class="day-color-{{ $v }}">{{ $v }}</div>
                    @endforeach
                </div>
                <div class="calendar__body">
                    @foreach($calendar as $date => $day)
                        @if($loop->first)
                            @foreach($days_names as $v)
                                @break($v === $day['dt']->format('D'))
                                <div class="calendar-cell"></div>
                            @endforeach
                        @endif

                        <div class="calendar-cell" data-date="{{ $date }}">
                            @include('layouts.calendar.tabs.schedule.calendar_item')
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

