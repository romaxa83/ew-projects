@php
    /**
     * @var $products \Illuminate\Support\Collection|array|\WezomCms\Catalog\Models\Product[]|null
     * @var $url string|null
     * @var $name string|null
     * @var $id string|null
     */

    $multiple = $multiple ?? false;
    $name = $name ?? ($multiple ? 'PRODUCTS[]' : 'product_id');
    if ($multiple && !str_ends_with($name, '[]')) {
        $name = $name.'[]';
    }
@endphp
<select name="{{ $name }}"
        id="{{ $id ?? $name }}"
        class="js-ajax-select2 form-control {{ $class ?? '' }}"
        @if($multiple) multiple="multiple" @endif
        data-template="product"
        data-url="{{ $url ?? route('admin.products.search', $multiple ? ['multiple' => true] : []) }}"
>
    @foreach($products ?? [] as $oneSelectOption)
        <option {!! Html::attributes([
                    'value' => $oneSelectOption->id,
                    'selected' => true,
                    'data-name' => $oneSelectOption->name,
                    'data-cost' => money($oneSelectOption->cost),
                    'data-currency' => money()->adminCurrencySymbol(),
                    'data-qty' => __('cms-catalog::admin.qty', ['qty' => $oneSelectOption->amount]),
                    'data-image' => $oneSelectOption->getImageUrl()]) !!}
        >{{ $oneSelectOption->name }}</option>
    @endforeach
</select>
