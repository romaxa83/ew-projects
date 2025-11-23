@php
    /**
     * @var $count int
     */
@endphp
<div>
    <div x-data="openModal({name:'orders.cart-popup'})" x-on:click="forceOpen">{{ $count }}</div>
</div>
