<div class="card m-auto border">
    <div class="card-header py-2 bg-primary-600">
        <div class="card-title">
            Estimate summary
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="row mb-2">
                <div class="col-6"><h5 class="mb-0">
                        Rate
                        @if($record->estimate->type === 'local')
                            (Hourly)
                        @elseif($record->estimate->type === 'intrastate')
                            (By weight)
                        @elseif($record->estimate->type === 'interstate')
                            ({{ ucfirst($record->estimate->{$record->estimate->type}->estimate_rate ?? 'N/A') }})
                        @endif
                    </h5></div>
                <div class="col-6 text-right fs-md">
                    ${{ $record->estimate->{$record->estimate->type}->rate ?? 'n/a' }}
                    @if($record->estimate->type === 'local')
                        / per hour (RPH)
                    @elseif($record->estimate->type === 'intrastate')
                        / per 100 lbs
                    @elseif($record->estimate->type === 'interstate')
                        / per 1 cbFt
                    @endif
                </div>
            </div>
            @foreach($record->calculated as $k => $v)
                @if(!in_array($k, ['total', 'paid', 'left2pay', 'overpaid', 'moving', 'labor']))
                    <div class="row mb-2">
                        <div class="col-6"><h5 class="mb-0">{{ $v->description }}</h5></div>
                        <div class="col-6 text-right fs-md">{{ $v->value }}</div>
                    </div>
                @endif
            @endforeach
{{--            <hr>--}}
{{--            <div class="row mb-2">--}}
{{--                <div class="col-6"><h5 class="mb-0 form-label">Total Estimate</h5></div>--}}
{{--                <div class="col-6 text-right fs-md">{{ $record->calculated['total']['value'] ?? 'n/a' }}</div>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
