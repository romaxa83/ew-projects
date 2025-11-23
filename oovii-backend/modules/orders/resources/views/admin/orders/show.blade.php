@php
    /**
     * @var $obj \WezomCms\Orders\Models\Order
     */
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Items list')</strong></h6>
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
                                <span class="js-item-purchase-price">@money($item->purchase_price, true)</span>
                            </td>
                            <td>
                                <span>{{ str_replace(',', '.', $item->quantity) }}</span> {{ $item->unit }}
                            </td>
                            <td>
                                <span>@money($item->whole_purchase_price, true)</span>
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
                                <span class="js-whole-purchase-price">
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
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Client')</strong></h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>{{ __('cms-orders::admin.orders.User') }}</dt>
                    @if($obj->user && Gate::allows('users.edit', $obj->user))
                        <dd><a href="{{ route('admin.users.edit', $obj->user->id) }}"
                               target="_blank">{{ $obj->user->full_name }}</a></dd>
                    @else
                        <dd>{{ $obj->user->full_name ?? __('cms-core::admin.layout.Not set') }}</dd>
                    @endif
                </dl>
                <div class="row">
                    <div class="col-md-6">
                        <dl>
                            <dt>{{ __('cms-orders::admin.orders.Name') }}</dt>
                            <dd>{{ $obj->client->name ?? __('cms-core::admin.layout.Not set') }}</dd>
                            <dt>{{ __('cms-orders::admin.orders.Email') }}</dt>
                            <dd>{{ $obj->client->email ?? __('cms-core::admin.layout.Not set') }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl>
                            <dt>{{ __('cms-orders::admin.orders.Surname') }}</dt>
                            <dd>{{ $obj->client->surname ?? __('cms-core::admin.layout.Not set') }}</dd>
                            <dt>{{ __('cms-orders::admin.orders.Phone') }}</dt>
                            <dd>{{ $obj->client->phone ?? __('cms-core::admin.layout.Not set') }}</dd>
                            @if($obj->dont_call_back)
                                <span class="text-info small">@lang('cms-orders::admin.orders.Dont call back')</span>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Recipient')</strong></h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>{{ __('cms-orders::admin.orders.Recipient') }}</dt>
                    <dd>{{ $obj->recipient->recipient_is_me ? __('cms-orders::admin.orders.Me') : __('cms-orders::admin.orders.Another man') }}</dd>
                </dl>
                <div class="row">
                    <div class="col-md-6">
                        <dl>
                            @if(!$obj->recipient->recipient_is_me)
                                <dt>{{ __('cms-orders::admin.orders.Name') }}</dt>
                                <dd>{{ $obj->recipient->name ?? __('cms-core::admin.layout.Not set') }}</dd>
                                <dt>{{ __('cms-orders::admin.orders.Patronymic') }}</dt>
                                <dd>{{ $obj->recipient->patronymic ?? __('cms-core::admin.layout.Not set') }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-md-6">
                        @if(!$obj->recipient->recipient_is_me)
                            <dl>
                                <dt>{{ __('cms-orders::admin.orders.Surname') }}</dt>
                                <dd>{{ $obj->recipient->surname ?? __('cms-core::admin.layout.Not set') }}</dd>
                                <dt>{{ __('cms-orders::admin.orders.Phone') }}</dt>
                                <dd>{{ $obj->recipient->phone ?? __('cms-core::admin.layout.Not set') }}</dd>
                            </dl>
                        @endif
                    </div>
                </div>
                <dl>
                    <dt>{{ __('cms-orders::admin.orders.Comment') }}</dt>
                    <dd>{!! nl2br($obj->recipient->comment ?? __('cms-core::admin.layout.Not set')) !!}</dd>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Status')</strong></h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <dl>
                        <dt>{{ __('cms-orders::admin.orders.Status') }}</dt>
                        <dd>{{ $status }}</dd>
                    </dl>
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
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Payment')</strong></h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>{{ __('cms-orders::admin.orders.Payment method') }}</dt>
                    <dd>{{ $payment }}</dd>
                    <dt>{{ __('cms-orders::admin.orders.Payed') }}</dt>
                    <dd>{{ $payed }}</dd>
                </dl>
                <div id="payment-data-wrapper">
                    {!! $paymentData !!}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="pt-1 pb-1"><strong>@lang('cms-orders::admin.orders.Delivery')</strong></h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>{{ __('cms-orders::admin.orders.Delivery method') }}</dt>
                    <dd>{{ $obj->delivery->name ?? __('cms-core::admin.layout.Not set') }}</dd>
                </dl>
                {!! $deliveryData !!}
            </div>
        </div>
    </div>
</div>
