{{--  NOT USED ANYMORE!!!  See Datatables render columnDefs   --}}
<div>
    <div class="tooltipOrderInfo">
        <a href="{{ route('orders.record', ['id' => $record->id]) }}"># {{ $record->id }}</a>
            <span class="text-muted fs-xs ml-2">{{ ucfirst($record->estimate->type) }}</span>
    </div>
{{--    <order-status order_id="{{$record->id}}" set-status-id="{{$record->status->id}}" set-interface="orders"></order-status>--}}
        <div class="text-nowrap font-weight-bold mt-1 mb-1" style="color: {{ $record->status->color }}">
            {{ $record->status->title }}
        </div>
    <div class="text-dark fs-nano">{{ Carbon\Carbon::parse($record->created_at)->format('M d, Y \a\t g:i A')}}</div>
</div>
