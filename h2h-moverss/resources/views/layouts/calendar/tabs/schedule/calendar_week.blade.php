<div class="card">
    <div class="card-body p-0">
        <div class="calendar-wrapper">
            <div class="calendar">
                <div class="calendar__row calendar__row--header">
                    @foreach($works_on_week['dates']['range'] as $v)
                        <div class="day-color-{{ $v['dt']->format('D') }}">
                            {{ $v['dt']->format('D') }}<br/>
                            <small>{{ $v['dt']->format('M d, Y') }}</small>
                        </div>
                    @endforeach
                </div>
                <div class="calendar__body" style="grid-template-rows: auto">
                    @foreach($works_on_week['dates']['range'] as $date => $v)
                        <div class="calendar-cell p-0">
                            @if(isset($works_on_week['records'][$date]))
                                @foreach($works_on_week['records'][$date] as $work)
                                    <div class="p-2">
                                        <div class="d-flex mb-1">
                                            <div class="width-1 height-1 rounded-circle position-absolute top-0"
                                                 style="background: {{ $work->order->status->color }};"></div>
                                            <div class="ml-5 mt-1">
                                                <a href="{{ route('orders.record', ['id' => $work->order->id]) }}"
                                                   target="_blank">#{{ $work->order->id }}</a>
                                                @if($work->order->payments_sum >= 100)
                                                    <span class="ml-2 fw-700 text-danger" title="Total paid: ${{ $work->order->payments_sum }}">$</span>
                                                @endif
                                                <br/>
                                            </div>
                                        </div>
                                        <div class="fs-nano fw-700 pt-2"
                                             style="color: {{ $work->order->status->color }};">
                                            {{ $work->order->status->title }}
                                        </div>
                                        <div class="text-dark mt-1 mb-1 fs-nano fw-500">
                                            ${{ $work->order->estimate->{$work->order->estimate->type}->rate ?? 'n/a' }}
                                            @if($work->order->estimate->type === 'local')
                                                / per hour (RPH)
                                            @elseif($work->order->estimate->type === 'intrastate')
                                                / per 100 lbs
                                            @elseif($work->order->estimate->type === 'interstate')
                                                / per 1 cbFt
                                            @endif
                                        </div>
                                        @if($work->timeWindow)
                                            <div
                                                class="badge badge-primary text-wrap mb-1">{{ $work->timeWindow }}</div>
                                            <br/>
                                        @endif

                                        @foreach($work->workTypes as $wt)
                                            <span class="badge badge-warning">{{ $wt->title }}</span>
                                        @endforeach
                                        <br/>

                                        <div class="text-info mt-1">
                                            {{ $work->order->client->name ?? 'No client' }}
                                        </div>
                                    </div>
                                    <hr class="m-0"/>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                    {{--                    @foreach($calendar as $date => $day)--}}
                    {{--                        @if($loop->first)--}}
                    {{--                            @foreach($days_names as $v)--}}
                    {{--                                @break($v === $day['dt']->format('D'))--}}
                    {{--                                <div class="calendar-cell"></div>--}}
                    {{--                            @endforeach--}}
                    {{--                        @endif--}}

                    {{--                        <div class="calendar-cell" data-date="{{ $date }}">--}}
                    {{--                            @include('layouts.calendar.tabs.schedule.calendar_item')--}}
                    {{--                        </div>--}}
                    {{--                    @endforeach--}}
                </div>
            </div>
        </div>
    </div>
</div>

