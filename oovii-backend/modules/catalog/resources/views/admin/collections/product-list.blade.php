@php
    /**
     * @var $product \WezomCms\Catalog\Models\Product
     */
@endphp
<div class="product-row-{{$product->id}} col-product-item" style="border: 1px solid #ffffff">
    <p>
        <button type="button" class="btn btn-danger delete-from-collection" data-product-id="{{$product->id}}">
            <i class="fa fa-trash delete-from-collection"></i>
        </button>

        {{ $product->name }}
    </p>
</div>

