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
                            (by weight)
                        @elseif($record->estimate->type === 'interstate')
                            ({{ ucfirst($record->estimate->{$record->estimate->type}->estimate_rate) }})
                        @endif
                    </h5></div>
                <div class="col-6 text-right fs-md">
                    ${{ $record->estimate->{$record->estimate->type}->rate ?? 'n/a' }}
                    @if($record->estimate->type === 'local')
                        / per hour
                    @elseif($record->estimate->type === 'intrastate')
                        / per 100 lbs
                    @elseif($record->estimate->type === 'interstate')
                        / per 1 cbFt
                    @endif
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><h5 class="mb-0">{{ $record->calculated['fee']->description }}</h5></div>
                <div class="col-6 text-right fs-md">{{ $record->calculated['fee']->value }}</div>
            </div>
        @if($record->estimate->type === 'local')
            <div class="row mb-2">
                <div class="col-6"><h5 class="mb-0">Estimated time</h5></div>
                <div class="col-6 text-right fs-md">{{ $record->estimate->{$record->estimate->type}->hours_min }} - {{ $record->estimate->{$record->estimate->type}->hours_max }} hrs</div>
            </div>
            @endif
            @if($record->estimate->type === 'intrastate')
                <div class="row mb-2">
                    <div class="col-6"><h5 class="mb-0">Weight</h5></div>
                    <div class="col-6 text-right fs-md">{{ $record->sizing_weight }} lbs</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><h5 class="mb-0">Miles</h5></div>
                    <div class="col-6 text-right fs-md">{{ $record->estimate->calculated_moving_distance }} mi</div>
                </div>
            @endif
            @if($record->estimate->type === 'interstate')
                <div class="row mb-2">
                    <div class="col-6"><h5 class="mb-0">Volume</h5></div>
                    <div class="col-6 text-right fs-md">{{ $record->sizing_volume }} cbFt</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><h5 class="mb-0 font-weight-bold">Subtotal</h5></div>
                    <div class="col-6 text-right fs-md">{{ $record->calculated['labor']->value }}</div>
                </div>
            @else
            <div class="row mb-2">
                <div class="col-6"><h5 class="mb-0 font-weight-bold">Subtotal</h5></div>
                <div class="col-6 text-right fs-md">{{ $record->calculated['moving']->value }}</div>
            </div>
            @endif
            @foreach($record->calculated as $k => $v)
                @if(!in_array($k, ['total', 'paid', 'left2pay', 'overpaid', 'moving', 'labor', 'fee', 'discount']))
                    <div class="row mb-2">
                        <div class="col-6"><h5 class="mb-0">{{ $v->description }}</h5></div>
                        <div class="col-6 text-right fs-md">{{ $v->value }}</div>
                    </div>
                @endif
            @endforeach
            @if(preg_replace("/[^0-9.]/", "", $record->calculated['discount']->value) != 0)
            <div class="row mb-2">
                <div class="col-6"><h5 class="mb-0">{{ $record->calculated['discount']->description }}</h5></div>
                <div class="col-6 text-right fs-md">{{ $record->calculated['discount']->value }}</div>
            </div>
            @endif
            <div class="row mb-2">
                <div class="col-6"><h5 class="mb-0 font-weight-bold">{{ $record->calculated['total']->description }}</h5></div>
                <div class="col-6 text-right fs-md">{{ $record->calculated['total']->value }}</div>
            </div>
{{--            <hr>--}}
{{--            <div class="row mb-2">--}}
{{--                <div class="col-6"><h5 class="mb-0 form-label">Total Estimate</h5></div>--}}
{{--                <div class="col-6 text-right fs-md">{{ $record->calculated['total']['value'] ?? 'n/a' }}</div>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
