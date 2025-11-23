@if($record->waypoints_count)
<div{!! $record->waypoints_count > 2 ? ' title="Multiple waypoints"':'' !!}>
    @foreach($record->waypoints as $waypoint)
        @if($loop->index && !$loop->last && $record->waypoints_count > 2)
            @continue
        @endif

        <div class="fs-xs">
            <div class="d-flex{!! $loop->first && $record->waypoints_count === 2 ? ' mb-2':'' !!}">
                <div class="mr-1">
                    @if($waypoint->type == 'pickup')
                        <span class="badge badge-secondary">P</span>
                    @elseif($waypoint->type == 'destination')
                        <span class="badge badge-success">D</span>
                    @endif
                </div>
                <div class="flex-grow-1 mb-sm-1">
                    {{ $waypoint->city }} {{ $waypoint->zip }}</a>
{{--                    {{ $waypoint->address }}, {{ $waypoint->city }}, {{ $waypoint->state }} {{ $waypoint->zip }}</a>--}}
                </div>
            </div>
            @if($loop->first && $record->waypoints_count > 2)
                <hr class="mt-0 mb-1"/>
            @endif
        </div>
    @endforeach
</div>
@else
    <div>
        No waypoints
    </div>
@endif
