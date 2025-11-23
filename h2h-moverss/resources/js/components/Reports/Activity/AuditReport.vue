<template>
	<div>
		<div class="row">
			<div class="col-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label">Period</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text fs-xl">
								<i class="fal fa-calendar"></i>
							</span>
						</div>
						<input
							type="text"
							class="form-control"
							placeholder="Select date"
							id="daterangepicker"
						/>
					</div>
				</div>
			</div>
			<div class="col-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label">Event Author</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text fs-xl">
								<i class="fal fa-user"></i>
							</span>
						</div>
						<input
							type="text"
							class="form-control"
							placeholder="Select date"
							id=""
						/>
					</div>
				</div>
			</div>
			<div class="col-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label">By Order</label>
					<vue-select2
						v-model="filter.orderID"
						:config="configSelect2ForOrder()"
					></vue-select2>
					<!--                    <input type="text" class="form-control" placeholder="Select date" id="">-->
				</div>
			</div>
			<div class="col-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label">By Client</label>
					<input
						type="text"
						class="form-control"
						placeholder="Select date"
						id=""
					/>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label">By Objects</label>
					<input
						type="text"
						class="form-control"
						placeholder="Select date"
						id=""
					/>
				</div>
			</div>
			<div class="col-4 col-xl-3 p-2">
				<div class="form-group">
					<label class="form-label">Event type</label>
					<input
						type="text"
						class="form-control"
						placeholder="Select date"
						id=""
					/>
				</div>
			</div>
			<div class="col-3 col-xl-2 p-2">
				<div class="form-group">
					<label class="form-label">&nbsp;</label><br />
					<button
						type="button"
						name="create"
						class="btn btn-primary waves-effect waves-themed"
					>
						Show Report
					</button>
				</div>

				<!--                <div class="form-group">-->
				<!--                    <label class="form-label" for="dateRangePicker">By Client</label>-->
				<!--                    <input type="text" class="form-control" placeholder="Select date" id="">-->
				<!--                </div>-->
			</div>
		</div>
		<hr />
	</div>
</template>

<script>
import VueSelect2 from '@components/VueSelect2';

export default {
	name: 'AuditReport',
	components: {
		VueSelect2,
	},
	data() {
		return {
			filter: {
				startDate: moment(),
				endDate: moment(),
				orderID: null,
			},
		};
	},
	mounted() {
		this.$nextTick(() => {
			this.initPlugins();
		});
	},
	methods: {
		configSelect2ForOrder() {
			const config = {
				multiple: true,
				// dropdownParent: $(this.$refs.inputInterfaceTask).parents('.modal-body:first'),
				containerCssClass:
					'border-bottom-left-radius-0 border-bottom-right-radius-0',
				selectionCssClass:
					'border-bottom-left-radius-0 border-bottom-right-radius-0',
				placeholder: 'Search Order (by id, client name, phones, email)',
				allowClear: true,
				ajax: {
					url: '/orders/autocomplete',
					method: 'POST',
					delay: 400,
					dataType: 'json',
					data(params) {
						return {
							q: params.term, // search term
							page: params.page || 1,
						};
					},
					// processResults(response, params) {
					//     return {
					//         results: response.data.results,
					//         pagination: response.data.pagination
					//     };
					// },
					cache: true,
				},
				escapeMarkup(markup) {
					return markup;
				},
				minimumInputLength: 0,
				templateResult: App.Miscs.templateSelect2Order,
				templateSelection: function (v) {
					if (v.id) return 'Order #' + v.id;
					// else if (el.data('route').includes('client') && v.name) {
					//     return v.name + ' ' + v.lname;
					// }
					return v.text;
				},
			};
			return config;
		},
		initPlugins() {
			$('#daterangepicker').daterangepicker(
				{
					minDate: moment('2020-01-01', 'YYYY-MM-DD'),
					maxDate: moment(),
					startDate: this.filter.startDate,
					endDate: this.filter.endDate,
					drops: 'auto',
					locale: {
						format: 'MMM DD, YYYY',
					},
					maxSpan: {
						days: 365,
					},
					alwaysShowCalendars: true,
					ranges: {
						Today: [moment(), moment()],
						Yesterday: [
							moment().subtract(1, 'days'),
							moment().subtract(1, 'days'),
						],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [
							moment().subtract(30, 'days'),
							moment(),
						],
					},
				},
				(start, end) => {
					this.filter.startDate = start.format('YYYY-MM-DD');
					this.filter.endDate = end.format('YYYY-MM-DD');
				}
			);
		},
	},
};
</script>
