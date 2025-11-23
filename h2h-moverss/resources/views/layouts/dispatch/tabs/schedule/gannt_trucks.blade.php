<div class="card">
    <div class="card-body p-0">
        <div class="frame-wrap position-absolute w-100 h-100 opacity-50 z-index-cloud d-none d-loader">
            <div class="w-100 d-flex justify-content-center align-items-center">
                <div class="spinner-border text-info position-absolute" style="top:50%;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="gantt-wrapper">
            <div class="gantt gantt-trucks">
                <div class="gantt__row gantt__row--header">
                    <div class="gantt__row-first"></div>
                    @foreach ($dispatchPeriod as $DateTime)
                        <span>{{$DateTime->format('g:i a')}}</span>
                    @endforeach
                </div>
                <div class="gantt__row gantt__row--lines-truck">
                    <span></span>
                    <span>
                        <div class="gantt__row gantt__row--lines-timeline">
                            @foreach ($dispatchPeriod as $k=>$DateTime)
                                <span class="c-start-{{$k+1}} c-span-1"></span>
                            @endforeach
                        </div>
                    </span>
                </div>
                {{--  строка с траком --}}

                @foreach ($trucks as $truckId => $truck)
                    <div class="gantt__row truck-row" data-truck-id="{{ $truckId }}">
                        <div class="gantt__row-first">
                            @if($canManage)
                                <div class="custom-control custom-checkbox truck-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="truck_{{$truckId}}">
                                    <label class="custom-control-label" for="truck_{{$truckId}}">
                                        {{ $truck->title }}
                                    </label>
                                </div>
                            @else
                                <div>{{ $truck->title }}</div>
                            @endif
                            <div>
                                <code style="color:{{ $truck->p_color  }}">
                                    {{ $truck->year }}
                                </code>
                            </div>
                            @if(!$truck->active)
                                <div class="text-danger mt-2">SOLD</div>
                            @endif
                        </div>
                        <div class="gantt__row-bars works-container">
                            @if(!empty($trucksBusy[$truckId]))
                                @foreach($trucksBusy[$truckId] as $busyData)
                                    <div class="cell busy occupied c-start-{{$busyData['from']}} c-span-{{$busyData['duration']}}">
                                        {{ $busyData['title'] }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
