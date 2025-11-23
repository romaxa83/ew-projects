<div class="card m-auto border">
    <div class="card-header py-2 bg-primary-600 d-flex">
        <div class="card-title">
            Extra materials & services
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table m-0 counter-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th class="w-50">Name</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($record->materials as $v)
                    <tr>
                        <td scope="row"></td>
                        <td>
                            {{ $v->title }}
                            @if($v->services)
                                <div class="fs-xs">
                                    ({{ $v->services }})
                                </div>
                            @endif
                        </td>
                        <td>{{ $v->qty }}</td>
                        <td>${{ $v->total_price }}</td>
                    </tr>
                @endforeach
                @foreach($record->customsExtras as $v)
                    <tr>
                        <td scope="row"></td>
                        <td>
                            {{ $v->title }}
                        </td>
                        <td>-</td>
                        <td>${{ $v->price }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
