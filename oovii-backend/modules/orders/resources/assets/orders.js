$(document).ready(function () {
    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }

    /**
     * @param itemWholePurchase
     * @param selector
     */
    function calcWholePurchasePrice(itemWholePurchase, selector) {
        selector = selector || '.js-whole-purchase-price';

        if ($(selector).length === 0) {
            return;
        }

        var wholePrice = 0;

        $(itemWholePurchase).each(function (i, el) {
            wholePrice += parseFloat($(el).val());
        });

        $(selector).html(numberWithCommas(Math.round(wholePrice * 100) / 100));
    }

    /**
     * @param rowScope
     * @param quantityInput
     * @param purchasePrice
     * @param wholePurchaseInput
     */
    function calcOrderItemCost(rowScope, quantityInput, purchasePrice, wholePurchaseInput) {
        if ($(rowScope).length === 0
            || $(quantityInput).length === 0
            || $(purchasePrice).length === 0
            || $(wholePurchaseInput).length === 0
        ) {
            return;
        }

        $(quantityInput).on('change keyup', function (e) {
            var $this = $(this);
            var $row = $this.closest(rowScope);

            var cost = parseFloat($row.find(purchasePrice).text().replace(',', '.')) * parseFloat($this.val());

            $row.find(wholePurchaseInput).val(Math.round(cost * 100) / 100);

            calcWholePurchasePrice(wholePurchaseInput);
        });
    }

    calcOrderItemCost(
        '.js-order-item-row',
        '.js-item-quantity',
        '.js-item-purchase-price',
        '.js-item-whole-purchase-price'
    );

    var $productSelector = $('.js-order-product-selector');

    $('#order_category_id').change(function () {
        var data = {};

        if ($(this).val()) {
            data.category_id = $(this).val();
        }
        $productSelector.val('');

        $productSelector.data('url', route('admin.products.search', data));

        inits.ajaxSelect2($productSelector);
    });

    $productSelector.change(function () {
        var selectedOption = $(this).select2('data')[0];
        var $unit = $('.js-order-product-quantity-unit');

        if (selectedOption.data) {
            $('.js-order-product-quantity').val(selectedOption.data.min)
                .attr('min', selectedOption.data.min)
                .attr('step', selectedOption.data.step);
        }

        $unit.html(selectedOption.data && selectedOption.data.unit ? selectedOption.data.unit : $unit.data('default'));
    });

	// Delivery block
	var $deliveryFormWrapper = $('#delivery-form-wrapper'),
		$deliverySelect = $('#delivery-select');

	function renderDeliveryForm() {
		var body = new FormData($deliveryFormWrapper.closest('form')[0]);
		body.delete('_method');

		axios.post(route('admin.orders.render-delivery-form'), body)
			.then((response) => {
				inits.ajaxSelect2(
					$deliveryFormWrapper.html(response.data.html).find('.js-ajax-select2')
				);
			});
	}

	$deliverySelect.on('change', renderDeliveryForm);

	// Nova Poshta
	$deliveryFormWrapper.on('change', '#np-city-select', function () {
		var val = $(this).val();
		var $npWarehouse = $('#np-branch-select');

		$npWarehouse.val('').prop('disabled', true);
		if (val) {
			axios.get(route('admin.nova-poshta.get-city-warehouses', {city_ref: val}))
				.then(function (response) {
					$npWarehouse.empty();
					$npWarehouse.select2({
						data: response.data.results
					});
					$npWarehouse.prop('disabled', false);
				});
		} else {
			$npWarehouse.empty().trigger('change');
		}
	});
});
