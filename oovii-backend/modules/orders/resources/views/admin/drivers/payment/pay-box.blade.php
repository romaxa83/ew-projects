@php
/**
 * @var $success bool
 * @var $cardOwner string|null
 * @var $cardNumber string|null
 * @var $cardBrand string|null
 * @var $paymentId string|null
 * @var $paymentLink string|null
 * @var $error string|null
 */
@endphp

<table class="table table-striped">
    <tbody>
        @if (isset($success))
            <tr class="{{ $success ? 'table-success' : 'table-danger' }}">
                <td>
                    @lang('cms-orders::admin.pay-box.Status')
                </td>
                <td>
                    {{ $success ? __('cms-orders::admin.pay-box.Success') : __('cms-orders::admin.pay-box.Error') }}
                </td>
            </tr>
        @endif
        @if ($cardBrand)
            <tr>
                <td>
                    @lang('cms-orders::admin.pay-box.Card brand')
                </td>
                <td>
                    {{ $cardBrand }}
                </td>
            </tr>
        @endif
        @if ($success)
            @if ($cardOwner)
                <tr>
                    <td>
                        @lang('cms-orders::admin.pay-box.Card owner')
                    </td>
                    <td>
                        {{ $cardOwner }}
                    </td>
                </tr>
            @endif
            @if ($cardNumber)
                <tr>
                    <td>
                        @lang('cms-orders::admin.pay-box.Card number')
                    </td>
                    <td>
                        {{ $cardNumber }}
                    </td>
                </tr>
            @endif
        @else
            @if($error)
                <tr>
                    <td>
                        @lang('cms-orders::admin.pay-box.Error')
                    </td>
                    <td>
                        {{ $error }}
                    </td>
                </tr>
            @endif
        @endif
        @if ($paymentId)
            <tr>
                <td>
                    @lang('cms-orders::admin.pay-box.Payment id')
                </td>
                <td>
                    {{ $paymentId }}
                </td>
            </tr>
        @endif
        @if ($paymentLink)
            <tr class="table-primary">
                <td colspan="2">
                    <a href="{{ $paymentLink }}" target="_blank" class="text-dark font-weight-bold">
                        @lang('cms-orders::admin.pay-box.Payment link')
                    </a>
                </td>
            </tr>
        @endif
    </tbody>
</table>
