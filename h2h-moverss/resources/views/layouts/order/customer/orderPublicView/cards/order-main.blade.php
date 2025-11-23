<div class="card border mb-3">
    <div class="card-header py-2 bg-primary-600">
        <div class="card-title">
            Order Information
        </div>
    </div>
    <div class="card-body">
        @if($record->estimate->type === 'local')
            <div class="mb-3">
                <h5 class="mb-0 form-label">
                    Rate (Hourly)
                </h5>
                <div class="text-muted">
                    ${{ $record->estimate->{$record->estimate->type}->rate ?? 'n/a' }}
                        / per hour (RPH)
                </div>
            </div>
        @else
            <div class="mb-3">
                <h5 class="mb-0 form-label">Minimum Estimated Amount</h5>
                <div class="text-muted">{{ $record->calculated['total']['value'] ?? 'n/a' }}</div>
            </div>
        @endif
        {{--        <div class="mb-3">--}}
        {{--            <h5 class="mb-0 form-label">--}}
        {{--                Rate--}}
        {{--                @if($record->estimate->type === 'local')--}}
        {{--                    (Hourly)--}}
        {{--                @elseif($record->estimate->type === 'intrastate')--}}
        {{--                    (By weight)--}}
        {{--                @elseif($record->estimate->type === 'interstate')--}}
        {{--                    ({{ ucfirst($record->estimate->{$record->estimate->type}->estimate_rate) }})--}}
        {{--                @endif--}}
        {{--            </h5>--}}
        {{--            <div class="text-muted">--}}
        {{--                ${{ $record->estimate->{$record->estimate->type}->rate ?? 'n/a' }}--}}
        {{--                @if($record->estimate->type === 'local')--}}
        {{--                    / per hour (RPH)--}}
        {{--                @elseif($record->estimate->type === 'intrastate')--}}
        {{--                    / per 100 lbs--}}
        {{--                @elseif($record->estimate->type === 'interstate')--}}
        {{--                    / per 1 cbFt--}}
        {{--                @endif--}}
        {{--            </div>--}}
        {{--        </div>--}}
        <div class="mb-3">
            <h5 class="mb-0 form-label">Date and Time Arrival Window</h5>
            @if($record->works_count === 1)
                <div class="text-muted">
                    {{ $record->works->first()->date }}
                </div>
            @else
                @foreach($record->works as $v)
                    <div class="text-muted">
                        {{ $v->workTypes->implode('title', ', ') .' - '. $v->date }}
                    </div>
                @endforeach
            @endif
        </div>
        <div class="mb-3">
            <h5 class="mb-0 form-label">Activities</h5>
            <div class="text-muted">{{ implode(', ', $record->works_all) }}</div>
        </div>
        <div class="mb-3">
            <h5 class="mb-0 form-label">Move type</h5>
            <div class="text-muted">{{ ucfirst($record->estimate->type) }}</div>
        </div>
        @if(!empty($record->estimate->calculated_moving_distance_auto) || !empty($record->estimate->calculated_moving_distance))
            <div class="mb-3">
                <h5 class="mb-0 form-label">Distance</h5>
                <div
                    class="text-muted">{{ $record->estimate->calculated_moving_distance ?: $record->estimate->calculated_moving_distance_auto }}
                    miles
                </div>
            </div>
        @endif
        <div class="mb-3">
            <h5 class="mb-0 form-label">Crew size</h5>
            <div class="text-muted">
                {{ trans_choice('site.plurals.employees', $record->estimate->crews) }},
                {{ trans_choice('site.plurals.truck', $record->estimate->trucks) }}
            </div>
        </div>
        <div class="mb-3">
            <h5 class="mb-0 form-label">Inventory</h5>
            <div class="text-muted">{{ $record->sizing_volume }} Cuft / {{ $record->sizing_weight }} lb</div>
        </div>
        @if($record->estimate->type === 'interstate')
            <div class="mb-3">
                <h5 class="mb-0 form-label">Delivery days</h5>
                <div class="text-muted">{{ $record->estimate->interstate->delivery_days ?? 'n/a' }}</div>
            </div>
        @endif
    </div>
</div>
