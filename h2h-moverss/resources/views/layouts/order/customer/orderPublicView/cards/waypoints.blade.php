<div class="card m-auto border">
    <div class="card-header py-2 bg-primary-600 d-flex">
        <div class="card-title">
            Waypoints
        </div>
        <div class="ml-auto">
            <div class="alert alert-secondary pt-0 pb-0 mb-0 px-3" role="alert">
                <i class="fas fa-map-marker-alt"></i> Distance: <b>{{$record->estimate->calculated_moving_distance}} mi</b>
            </div>
        </div>
    </div>
    <div class="card-body">
        <ul class="list-unstyled">
            @foreach($record->waypoints as $v)
                <li{!! !$loop->last ? ' class="mb-2"':'' !!}>
                    <small>{{ ucfirst($v->type) }}</small>
                    <h5 class="mb-0 form-label"><i class="fa fa-map-marker-alt"></i>
                        {{ $v->getRouteName() }}
                    </h5>
                    <div class="text-muted">
                        {{ $v->buildingType->title.($v->ap ? " #{$v->ap}":'') }},
                        {{ $v->parkingType->title }},
                        {{ $v->has_elevator ? 'Elevator, ':'' }}
                        {{ $v->flights_id && $v->flights ? $v->flights->title : ''  }}
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
