<div class="row dispatch-popover-orders mt-2 mb-2">
    @foreach($records as $work)
        <div class="col">
            <a href="{{ route('orders.record', ['id' => $work->order->id]) }}">
                # {{ $work->order->id }}
                @if($work->order->payments_sum >= 100)
                    <span class="ml-2 fw-700 text-danger" title="Total paid: ${{ $work->order->payments_sum }}">$</span>
                @endif

                <span class="fs-nano ml-2 fw-700" style="color: {{ $work->order->status->color }}">
                    {{ $work->order->status->title }}
                </span>
            </a><br/>
            <small
                class="text-muted">Created: {{ Carbon\Carbon::parse($work->order->created_at)->format('m/d/Y')}}</small>
        </div>
        <div class="col">
            <b>Customer Name:</b> {{ $work->order->client ? $work->order->client->ClientShortName() : 'None' }}
        </div>
        <div class="col">
            {!! $work->start_at !!}
        </div>
        <div class="col">
            <b>${{ $work->order->estimate->{$work->order->estimate->type}->rate ?? 'n/a' }}</b>
            @if($work->order->estimate->type === 'local')
                / per hour
            @elseif($work->order->estimate->type === 'intrastate')
                / per 100 lbs
            @elseif($work->order->estimate->type === 'interstate')
                / per 1 cbFt
            @endif
            <br/>
            <b>Travel Fee:</b> {{ $work->order->calculated['fee']['value'] ?? 'n/a' }}
        </div>
        <div class="col">
            <b>Move Type:</b> {{ ucfirst($work->order->estimate->type) }}
        </div>
        <div class="col">
            @foreach($work->workTypes as $workType)
                <button type="button"
                        class="btn btn-xs btn-primary waves-effect waves-themed mb-1">{{$workType->title}}</button>
                <br/>
            @endforeach
        </div>
        <div class="col">
            <b>Trucks:</b> {{ $work->trucks ?? 'not set' }}<br/>
            <b>Crew:</b> {{ $work->employees ?? 'not set' }}
        </div>

        @if (!$loop->last)
            <div class="w-100 mb-2">
                <hr/>
            </div>
        @endif
    @endforeach
</div>

