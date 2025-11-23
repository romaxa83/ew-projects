@php
    /**
     * @var $count int
     */
@endphp
<div>
    <button type="button" data-cart-target='{"action":"open"}'>
        <span data-cart-count {{ $count === 0 ? 'hidden' : '' }}>{{ $count }}</span>
    </button>
</div>
