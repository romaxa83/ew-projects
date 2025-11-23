@extends('cms-ui::layouts.main')

@php
    /**
     * @var $text1 string
     * @var $text2 string
     * @var $text3 string
     * @var $deliveryVariants \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\DeliveryVariant[]
     * @var $paymentVariants \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\PaymentVariant[]
     */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div>
            <div>@lang('cms-orders::site.Способы доставки')</div>
            <div class="wysiwyg js-import" data-wrap-media data-draggable-table>
                {!! $text1 !!}
            </div>
            @if($deliveryVariants->isNotEmpty())
                @foreach($deliveryVariants as $delivery)
                    <div>
                        @if($delivery->imageExists())
                            <img src="{{ $delivery->getImageUrl() }}" alt="{{ $delivery->name }}">
                        @endif
                        @if($delivery->text)
                            <div>{!! nl2br(e($delivery->text)) !!}</div>
                        @endif
                    </div>
                @endforeach
            @endif
            <div class="wysiwyg js-import" data-wrap-media data-draggable-table>
                {!! $text2 !!}
            </div>
        </div>

        <div>
            <div>@lang('cms-orders::site.способы оплаты')</div>
            @if($paymentVariants->isNotEmpty())
                @foreach($paymentVariants as $payment)
                    <div>
                        @if($payment->imageExists())
                            <img src="{{ $payment->getImageUrl() }}" alt="{{ $payment->name }}">
                        @endif
                        @if($payment->text)
                            <div>{!! nl2br(e($payment->text)) !!}</div>
                        @endif
                    </div>
                @endforeach
            @endif
            <div class="wysiwyg js-import" data-wrap-media data-draggable-table>
                {!! $text3 !!}
            </div>
        </div>
    </div>
@endsection
