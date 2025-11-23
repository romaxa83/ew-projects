@php
    /**
     * @var $items array
     */
@endphp
<div>
    <ul>
        @foreach($items as $item)
            @php
                /** @var \WezomCms\Orders\Contracts\PurchasedProductInterface $product */
                $product = $item['product'];
            @endphp
            <li>
                <div>
                    <div>
                        <div>
                            <a href="{{ $item['url'] }}">
                                <img src="{{ $item['src'] }}" alt="{{ $item['name'] }}">
                            </a>
                        </div>
                        <div>
                            <div>
                                <div>
                                    <button wire:loading.attr="disabled"
                                            wire:click="decreaseCount('{{ $item['row_id'] }}')"
                                            @if(!$product->canDecreaseQuantity($item['quantity']['value'])) disabled @endif
                                    >
                                        -
                                    </button>
                                    <input type="number"
                                           wire:model.lazy="content.{{ $item['row_id'] }}"
                                           value="{{ $item['quantity']['value'] }}"
                                           min="{{ $item['quantity']['min'] }}"
                                           step="{{ $item['quantity']['step'] }}"
                                    >
                                    <button
                                        wire:loading.attr="disabled"
                                            wire:click="increaseCount('{{ $item['row_id'] }}')"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            <div>
                                <button
                                    wire:loading.attr="disabled"
                                        wire:click="removeItem('{{ $item['row_id'] }}')"
                                >
                                    <span>@lang('cms-orders::site.Удалить')</span>
                                    &cross;
                                </button>

                                <div>
                                    @if($item['crossed_out_sub_total'] > $item['sub_total'])
                                        <s>@money($item['crossed_out_sub_total'], true)</s>
                                    @endif
                                    <div>@money($item['sub_total'], true)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
