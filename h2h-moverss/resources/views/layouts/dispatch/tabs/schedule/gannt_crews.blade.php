<div class="card">
    <div class="card-body p-0" style="min-width: 1340px;">
        <div class="frame-wrap position-absolute w-100 h-100 opacity-50 z-index-cloud d-none d-loader">
            <div class="w-100 d-flex justify-content-center align-items-center">
                <div class="spinner-border text-info position-absolute" style="top:50%;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="gantt-wrapper">
            <div class="gantt gantt-crews">
                <div class="gantt__row gantt__row--header">
                    <div class="gantt__row-first"></div>
                    @foreach ($dispatchPeriod as $DateTime)
                        <span>{{$DateTime->format('g:i a')}}</span>
                    @endforeach
                </div>
                <div class="gantt__row gantt__row--lines-crew">
                    <span></span>
                    <span>
                        <div class="gantt__row gantt__row--lines-timeline">
                            @foreach ($dispatchPeriod as $k=>$DateTime)
                                <span class="c-start-{{$k+1}} c-span-1"></span>
                            @endforeach
                        </div>
                    </span>
                </div>
                {{--  строка с работником --}}
                @foreach ($workers as $workerId => $workerData)
                    <div class="gantt__row employee-row" data-employee-id="{{$workerId}}">
                        <div class="gantt__row-first">
                            <div class="custom-control custom-checkbox crew-checkbox">
                                <input type="checkbox" class="custom-control-input" id="crew_{{$workerId}}">
                                <label class="custom-control-label" for="crew_{{$workerId}}">
                                    {{ $workerData->name .' '.$workerData->l_name }}
                                </label>
                            </div>
                            <div class="text-primary">
                                {{ isset($workerData->user->roles) ? $workerData->user->roles->pluck('title')->implode(', ') : 'n/a' }}
                            </div>
                            @if(!$workerData->active)
                                <div class="text-danger mt-2">FIRED</div>
                            @endif
                        </div>
                        <div class="gantt__row-bars">
                            @if(!empty($workersBusy[$workerId]))
                                @foreach($workersBusy[$workerId] as $busyData)
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
