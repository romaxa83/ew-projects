@php
    /**
     * @var $obj \WezomCms\Orders\Models\Order
     */
@endphp
<div class="row">
    @if($obj->exists)
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">@lang('cms-orders::admin.orders.Items list')</div>
                    <a href="{{ route('admin.orders.add-item', [ 'order' => $obj->id, 'provider' => $obj->provider_id ]) }}"
                       class="btn btn-primary">@lang('cms-orders::admin.orders.Add item')</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>@lang('cms-orders::admin.orders.Image')</th>
                            <th>@lang('cms-orders::admin.orders.Product name')</th>
                            <th>@lang('cms-orders::admin.orders.Price')</th>
                            <th>@lang('cms-orders::admin.orders.Purchase price')</th>
                            <th>@lang('cms-orders::admin.orders.Quantity')</th>
                            <th>@lang('cms-orders::admin.orders.Total')</th>
                            <th width="1%" class="text-center">@lang('cms-core::admin.layout.Manage')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($obj->items as $item)
                            <tr class="js-order-item-row">
                                <td>
                                    @if($item->product && !$item->product->trashed() && Gate::allows('products.edit', $item->product))
                                        <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                           target="_blank">{{ $item->product_id }}</a>
                                    @else
                                        {{ $item->product_id }}
                                    @endif
                                </td>
                                <td>
                                    @if($item->product && $item->product->imageExists())
                                        <a href="{{ $item->product->getImageUrl() }}" data-fancybox>
                                            <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}"
                                                 height="50">
                                        </a>
                                    @else
                                        <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}" height="50">
                                    @endif
                                </td>
                                <td>
                                    @if($item->product && !$item->product->trashed() && Gate::allows('products.edit', $item->product))
                                        <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                           target="_blank">{{ $item->name }}</a>
                                    @else
                                        {{ $item->name }}
                                    @endif
                                </td>
                                <td>@money($item->price, true)</td>
                                <td>
                                    <span class="js-item-purchase-price">{{ $item->purchase_price }}</span> {{ money()->adminCurrencySymbol() }}
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="QUANTITY[{{ $item->id }}]"
                                               value="{{ $item->quantity }}"
                                               min="{{ $item->product ? $item->product->minCountForPurchase() : 1 }}"
                                               step="{{ $item->product ? $item->product->stepForPurchase() : 1 }}"
                                               placeholder="@lang('cms-orders::admin.orders.Quantity')"
                                               class="form-control js-item-quantity">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ $item->unit }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" readonly="readonly"
                                               value="{{ str_replace(',', '.', round($item->whole_purchase_price, money()->precision())) }}"
                                               placeholder="@lang('cms-orders::admin.orders.Total')"
                                               class="form-control js-item-whole-purchase-price">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ money()->adminCurrencySymbol() }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route($routeName . '.delete-item', [$obj->id, $item->id]) }}"
                                       title="@lang('cms-core::admin.layout.Delete')" class="btn btn-danger"
                                       onclick="return confirmDelete(this)"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"></td>
                                <td colspan="2">
                                    @lang('cms-orders::admin.delivery-and-payment.Delivery cost')
                                </td>
                                <td>
                                    <span>
                                        {{ number_format($obj->getDeliveryCost(), money()->precision(), '.', ' ') }}
                                    </span> {{ money()->adminCurrencySymbol() }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td colspan="2">
                                    @lang('cms-orders::admin.orders.Total')
                                </td>
                                <td>
                                    <span class="js-whole-purchase-price">
                                        {{ number_format($obj->getTotalSum(), money()->precision(), '.', ' ') }}
                                    </span> {{ money()->adminCurrencySymbol() }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Client')</strong></h6>
            </div>
            <div class="card-body">
                @if(WezomCms\Core\Foundation\Helpers::providerLoaded(\WezomCms\Users\UsersServiceProvider::class))
                    <div class="form-group">
                        {!! Form::label('user_id', __('cms-orders::admin.orders.User')) !!}
                        {!! Form::select('user_id', $users, null, ['class' => 'js-ajax-select2', 'data-url' => route('admin.users.search')])  !!}
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('client[surname]', __('cms-orders::admin.orders.Surname')) !!}
                            {!! Form::text('client[surname]', old('client.surname', optional($obj->client)->surname))  !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('client[name]', __('cms-orders::admin.orders.Name')) !!}
                            {!! Form::text('client[name]', old('client.name', optional($obj->client)->name))  !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('client[patronymic]', __('cms-orders::admin.orders.Patronymic')) !!}
                            {!! Form::text('client[patronymic]', old('client.patronymic', optional($obj->client)->patronymic))  !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('client[email]', __('cms-orders::admin.orders.Email')) !!}
                            {!! Form::text('client[email]', old('client.email', optional($obj->client)->email))  !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('client[phone]', __('cms-orders::admin.orders.Phone')) !!}
                            {!! Form::text('client[phone]', old('client.phone', optional($obj->client)->phone))  !!}
                            @if($obj->dont_call_back)
                                <span class="text-info small">@lang('cms-orders::admin.orders.Dont call back')</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Recipient')</strong></h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="custom-control custom-radio custom-control-inline">
                        {!! Form::radio('recipient[recipient_is_me]', 1, null, ['class' => 'custom-control-input', 'id' => 'recipient.recipient_is_me.1']) !!}
                        {!! Form::label('recipient.recipient_is_me.1', __('cms-orders::admin.orders.Me'), ['class' => 'custom-control-label']) !!}
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        {!! Form::radio('recipient[recipient_is_me]', 0, null, ['class' => 'custom-control-input', 'id' => 'recipient.recipient_is_me.0']) !!}
                        {!! Form::label('recipient.recipient_is_me.0', __('cms-orders::admin.orders.Another man'), ['class' => 'custom-control-label']) !!}
                    </div>
                </div>
                <hr>
                <div id="recipient-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('recipient[name]', __('cms-orders::admin.orders.Name')) !!}
                                {!! Form::text('recipient[name]', old('recipient.name', optional($obj->recipient)->name))  !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('recipient[surname]', __('cms-orders::admin.orders.Surname')) !!}
                                {!! Form::text('recipient[surname]', old('recipient.surname', optional($obj->recipient)->surname))  !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{--<div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('recipient[patronymic]', __('cms-orders::admin.orders.Patronymic')) !!}
                                {!! Form::text('recipient[patronymic]', old('recipient.patronymic', optional($obj->recipient)->patronymic))  !!}
                            </div>
                        </div>--}}
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('recipient[phone]', __('cms-orders::admin.orders.Phone')) !!}
                                {!! Form::text('recipient[phone]', old('recipient.phone', optional($obj->recipient)->phone))  !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('recipient[email]', __('cms-orders::admin.orders.Email')) !!}
                                {!! Form::text('recipient[email]', old('recipient.email', optional($obj->recipient)->email)) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('recipient[comment]', __('cms-orders::admin.orders.Comment')) !!}
                    {!! Form::textarea('recipient[comment]', old('recipient.comment', optional($obj->recipient)->comment), ['rows' => 4])  !!}
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Status')</strong></h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::select('status_id', $statuses)  !!}
                </div>
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th width="1%">#</th>
                        <th>@lang('cms-orders::admin.orders.Status')</th>
                        <th>@lang('cms-orders::admin.orders.Changed')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($obj->statusHistory as $status)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $status->name }}</td>
                            <td>{{ $status->pivot->created_at }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Provider')</strong></h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('provider_id', __('cms-orders::admin.orders.Provider')) !!}
                    {!! Form::select(
                        'provider_id',
                        $providers,
                        old('provider_id', $obj->provider_id ?? null),
                        [ 'disabled' => $obj->exists ]
                    )  !!}
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Payment')</strong></h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('payment_id', __('cms-orders::admin.orders.Payment method')) !!}
                    {!! Form::select('payment_id', $payments)  !!}
                </div>
                <div class="form-group">
                    {!! Form::label('payed', __('cms-orders::admin.orders.Payed')) !!}
                    {!! Form::status('payed') !!}
                    @if($obj->payed)
                        <span class="text-info small">@lang('cms-orders::admin.orders.Payment status is set: :mode :time', ['mode' => \WezomCms\Orders\Enums\PayedModes::getDescription($obj->payed_mode), 'time' => $obj->payed_at])</span>
                    @endif
                </div>
                <div id="payment-data-wrapper">
                    {!! $paymentData !!}
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Delivery')</strong></h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('delivery_id', __('cms-orders::admin.orders.Delivery method')) !!}
                    {!! Form::select('delivery_id', $deliveries, null, ['id' => 'delivery-select'])  !!}
                </div>
                <div id="delivery-form-wrapper">
                    {!! $deliveryForm !!}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            var isMeButtons = $('input[name="recipient[recipient_is_me]"]');
            var isMeValue = $('input[name="recipient[recipient_is_me]"]:checked').val();

            showRecipient(isMeValue);

            function showRecipient(isMe) {
                var recipientData = $('#recipient-data');

                if (isMe > 0) {
                    recipientData.hide();
                } else {
                    recipientData.show();
                }
            }

            isMeButtons.on('change', function () {
                isMeValue = $('input[name="recipient[recipient_is_me]"]:checked').val();

                showRecipient(isMeValue);
            });
        });
    </script>
@endpush
