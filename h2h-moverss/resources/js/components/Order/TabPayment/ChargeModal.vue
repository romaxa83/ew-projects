<template>
	<div
		class="modal fade"
		id="modal-order-charge-modal"
		role="dialog"
		aria-hidden="true"
	>
		<div
			class="modal-dialog modal-xl modal-dialog-centered"
			role="document"
		>
			<div class="modal-content p-4">
				<authorize-online-charge
					:order_id="order_id"
					:branch_id="branch_id"
				></authorize-online-charge>
			</div>
		</div>
	</div>
</template>

<script>
import AuthorizeOnlineCharge from '../../Reports/Authorize/OnlineCharge';

let order_id = parseInt(document.getElementById('order_id').textContent);

export default {
	name: 'ChargeModal',
	components: { AuthorizeOnlineCharge },
	mounted() {
		$('#modal-order-charge-modal').modal('show');
	},
	data() {
		return {
			order_id,
			branch_id: parseInt($('#division_id').val()),
		};
	},
};
</script>
