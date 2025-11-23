<div class="card border mb-3">
    <div class="card-header py-2 bg-primary-600">
        <div class="card-title">
            Order Information
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <h5 class="mb-0 form-label">Date and Time Arrival</h5>
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
            <h5 class="mb-0 form-label">Job Description</h5>
            <div class="text-muted">{{ implode(', ', $record->works_all) }}</div>
        </div>
        <div class="mb-3">
            <h5 class="mb-0 form-label">Move type</h5>
            <div class="text-muted">{{ ucfirst($record->estimate->type) }}</div>
        </div>
        @if($record->is_estimate_available)
        <div class="mb-3">
            <h5 class="mb-0 form-label">Crew size</h5>
            <div class="text-muted">
                {{ trans_choice('site.plurals.employees', $record->estimate->crews) }},
                {{ trans_choice('site.plurals.truck', $record->estimate->trucks) }}
            </div>
        </div>
        @endif


{{--        @if(!empty($record->estimate->calculated_moving_distance_auto) || !empty($record->estimate->calculated_moving_distance))--}}
{{--            <div class="mb-3">--}}
{{--                <h5 class="mb-0 form-label">Distance</h5>--}}
{{--                <div--}}
{{--                    class="text-muted">{{ $record->estimate->calculated_moving_distance ?: $record->estimate->calculated_moving_distance_auto }}--}}
{{--                    miles--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}
    </div>
</div>
