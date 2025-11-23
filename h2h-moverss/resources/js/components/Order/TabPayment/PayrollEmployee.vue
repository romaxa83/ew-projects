<template>
	<div class="panel" ref="payrollPanel">
		<div class="panel-hdr">
			<div class="flex-grow-1">
				<h2>
					<a v-if="listVariant" :href="orderLink" class="fw-500">
						{{ title }}
					</a>
					<span v-else>{{ title }}</span>
				</h2>
				<div
					v-if="!isEditing"
					:class="{
						'payroll-status': true,
						'payroll-status--processed': payroll.is_processed,
					}"
				>
					<div class="payroll-status__icon" aria-hidden="true">
						<svg width="15" height="14" viewBox="0 0 15 15">
							<use :href="statusIcon" />
						</svg>
					</div>
					<span class="payroll-status__text">{{ statusText }}</span>
					<span v-if="payroll.is_processed">
						by
						<a
							v-if="canViewEmployeeCard"
							:href="employeeLink"
							class="fw-500"
						>
							{{ employeeName }}
						</a>
						<strong class="fw-500" v-else>
							{{ employeeName }}
						</strong>
					</span>
					<span v-if="payroll.is_processed && !!processedAt">
						({{ processedAt }})
					</span>
					<span v-if="listVariant && payroll.client" class="ml-3">
						Client: {{ payroll.client.name }} ({{
							payroll.client.id
						}})
					</span>
					<span v-if="listVariant" class="ml-3"
						>Date: {{ orderDate }}</span
					>
				</div>
			</div>
			<div v-if="listVariant" class="panel-toolbar">
				<button
					class="btn btn-outline-info shadow-0 waves-effect waves-themed collapse-btn collapsed"
					data-toggle="collapse"
					:data-target="`#payroll-collapse-${payroll.id}`"
				>
					<svg viewBox="0 0 14 14" class="less">
						<use :href="getCollapseIcon('collapse')" />
					</svg>
					<svg viewBox="0 0 14 14" class="more">
						<use :href="getCollapseIcon('expand')" />
					</svg>
					<span class="less">Show less</span>
					<span class="more">Show more</span>
				</button>
			</div>
			<div v-else-if="showActions" class="panel-toolbar">
				<label
					v-if="canSwitch"
					class="custom-control custom-switch processed-switcher"
					:class="{ disabled: isLoading }"
				>
					<span class="fw-500">{{ markAsLabel }}</span>
					<span class="switcher">
						<input
							type="checkbox"
							class="custom-control-input"
							v-model="isProcessed"
							@change="toggleProcessing"
							:disabled="isLoading"
						/>
						<span class="custom-control-label"></span>
					</span>
				</label>
				<div v-if="canEdit && canSwitch" class="vertical-divider"></div>
				<button
					class="btn btn-secondary shadow-0 waves-effect waves-themed"
					@click="openEditing"
					v-if="canEdit"
				>
					Edit
				</button>
			</div>
		</div>

		<div class="panel-container">
			<div class="panel-content payroll-payments-content">
				<div class="row">
					<div class="col-sm-6">
						<div class="group-title">Received from client</div>
						<div class="value-group">
							<p>Cash collected, $</p>
							<input
								v-if="isEditing"
								class="form-control"
								type="number"
								min="0"
								v-model.number="cashCollected"
								@blur="cashCollected = cashCollected || 0"
							/>
							<p v-else>{{ formatNumber(cashCollected) }}</p>
						</div>
						<div class="value-group">
							<p>Zelle, $</p>
							<p>{{ formatNumber(payroll.zelle) }}</p>
						</div>
						<div class="value-group">
							<p>Credit card, $</p>
							<p>{{ formatNumber(payroll.credit_card) }}</p>
						</div>
						<div class="value-group">
							<p>Credit card fee, $</p>
							<p>{{ formatNumber(payroll.credit_card_fee) }}</p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="group-title">Paid to team</div>
						<div class="value-group">
							<p>Margin %</p>
							<p>
								{{ margin }}% ({{ formatNumber(marginCash) }})
							</p>
						</div>
						<div class="value-group">
							<p>Sum cash paid, $</p>
							<p class="text-lg">
								{{ formatNumber(sumCashPaid) }}
							</p>
						</div>
						<div class="value-group">
							<p>Sum cc paid, $</p>
							<p class="text-lg">
								{{ formatNumber(sumCCPaid) }}
							</p>
						</div>
						<div v-if="listVariant" class="value-group">
							<p>Cash on hands in result, $</p>
							<p>{{ formatNumber(cashOnHands) }}</p>
						</div>
					</div>
				</div>
			</div>

			<div v-if="isEditing" class="panel-content">
				<p class="fw-500 mb-4">By movers:</p>
				<form>
					<div v-for="item of items" class="employee-block">
						<div class="row align-items-center mb-1">
							<div class="col">
								<p class="employee-name fw-500 m-0">
									{{ item.employee_name }}
								</p>
								<div class="form-group">
									<select
										v-model.number="item.role_id"
										type="text"
										class="custom-select custom-select-sm"
									>
										<option
											v-for="role of employeeRoles"
											:key="role.id"
											v-bind:value="role.id"
										>
											{{ role.title }}
										</option>
									</select>
								</div>
							</div>
							<div class="col-auto">
								<b-form-radio-group
									v-model="item.paymentType"
									:options="[
										{ text: 'Cash', value: 'cash' },
										{ text: 'CC', value: 'cc_due' },
									]"
									button-variant="outline-primary"
									name="payment-type"
									size="sm"
									buttons
								></b-form-radio-group>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-6">
								<div class="value-group">
									<p>Hours</p>
									<input
										class="form-control"
										type="text"
										v-model="item.hours"
										:id="`payroll-employee-hours-${item.employee_id}`"
										@blur="
											item.hours = item.hours
												? item.hours.replaceAll(
														'_',
														'0'
												  )
												: '0:00'
										"
									/>
								</div>
								<div class="value-group">
									<p>Subtotal, $</p>
									<p>{{ formatNumber(item.sub_total) }}</p>
								</div>
								<div class="value-group">
									<p>
										{{ getCorrespondingSumPaidLabel(item) }}
									</p>
									<p class="text-lg">
										{{ getCorrespondingSumPaidValue(item) }}
									</p>
								</div>
							</div>

							<div class="col-sm-6">
								<div class="value-group">
									<p>Hourly rate, $</p>
									<input
										class="form-control"
										type="number"
										min="0"
										v-model.number="item.hourly_rate"
										@blur="
											item.hourly_rate =
												item.hourly_rate || 0
										"
									/>
								</div>
								<div class="value-group">
									<p>Extras, $</p>
									<input
										class="form-control"
										type="number"
										min="0"
										v-model.number="item.extras"
										@blur="item.extras = item.extras || 0"
									/>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div class="row pt-2">
					<div class="col-auto ml-auto">
						<button
							@click="submit"
							type="button"
							class="btn btn-success"
							:disabled="isLoading"
						>
							<span
								v-show="isLoading"
								class="spinner-border spinner-border-sm"
								role="status"
								aria-hidden="true"
							></span>
							Save
						</button>
					</div>
				</div>
			</div>
			<div
				v-else
				:id="`payroll-collapse-${payroll.id}`"
				:class="{ collapse: listVariant }"
			>
				<div class="panel-content">
					<table class="table table-bordered m-0">
						<thead>
							<tr>
								<th :width="tableColumnWidths.employee">
									Mover
								</th>
								<th :width="tableColumnWidths.hours">Hours</th>
								<th :width="tableColumnWidths.rate">
									Hourly rate, $
								</th>
								<th :width="tableColumnWidths.subtotal">
									Subtotal, $
								</th>
								<th :width="tableColumnWidths.extras">
									Extras, $
								</th>
								<th :width="tableColumnWidths.cashPaid">
									Cash paid, $
								</th>
								<th :width="tableColumnWidths.ccPaid">
									CC due, $
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item of payroll.items">
								<td>
									<a
										v-if="canViewEmployeeCard"
										:href="
											getEmployeeLink(item.employee_id)
										"
										class="fw-500"
									>
										{{ item.employee_name }}
									</a>
									<strong class="fw-500" v-else>
										{{ item.employee_name }}
									</strong>
									<p class="text-secondary m-0">
										{{ item.role_name }}
									</p>
								</td>
								<td>{{ formatNumberToHours(item.hours) }}</td>
								<td>{{ formatNumber(item.hourly_rate) }}</td>
								<td>{{ formatNumber(item.sub_total) }}</td>
								<td>{{ formatNumber(item.extras) }}</td>
								<td class="text-lg">
									{{ formatNumber(item.cash_paid) }}
								</td>
								<td class="text-lg">
									{{ formatNumber(item.cc_due_paid) }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div
			v-if="isLoading"
			class="frame-wrap position-absolute w-100 h-100 opacity-50"
		>
			<div class="w-100 d-flex justify-content-center align-items-center">
				<div
					class="spinner-border text-info position-absolute"
					role="status"
				>
					<span class="sr-only">Loading...</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { BFormRadioGroup } from 'bootstrap-vue';
import moment from 'moment';
import { DateService } from '@/services/date';

export default {
	name: 'PayrollEmployee',
	components: { BFormRadioGroup },
	filters: {},
	props: {
		payroll: {
			type: Object,
			default: () => ({}),
		},
		canViewEmployeeCard: {
			type: Boolean,
			required: true,
		},
		listVariant: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			svgSpritePath: '/images/payroll-sprite.svg?cache=2',
			isEditing: false,
			isLoading: false,
			isProcessed: this.payroll.is_processed,
			cashCollected: Number(this.payroll.cash) || 0,
			items:
				this.payroll.items?.map((item) => ({
					...item,
					paymentType: item.is_cc_due ? 'cc_due' : 'cash',
					hours: this.formatNumberToHours(item.hours),
				})) || [],
			isCollapsed: true,
		};
	},
	watch: {
		items: {
			deep: true,
			handler(newItems) {
				newItems.forEach((item) => {
					const hours = this.formatHoursToNumber(item.hours);
					const subtotal = hours * item.hourly_rate;
					const paid = subtotal + item.extras;
					const isCCDue = item.paymentType === 'cc_due';

					item.is_cc_due = isCCDue;
					item.sub_total = subtotal;
					item.cc_due_paid = isCCDue ? paid : 0;
					item.cash_paid = !isCCDue ? paid : 0;
					// item.hours = this.formatNumberToHours(hours);
				});
			},
		},
	},
	computed: {
		canEdit() {
			return !!this.payroll.meta?.actions?.can_edit;
		},
		canSwitch() {
			return !!this.payroll.meta?.actions?.can_switch;
		},
		showActions() {
			return !this.isEditing && (this.canSwitch || this.canEdit);
		},
		title() {
			if (this.listVariant) {
				return `Order #${this.payroll.order_id}`;
			}

			return 'Payroll';
		},
		employeeRoles() {
			return (
				Object.entries(this.payroll.meta.roles || {}).map(
					([key, value]) => ({
						id: key,
						title: value,
					})
				) || []
			);
		},
		employeeLink() {
			if (this.payroll.is_processed) {
				return this.getEmployeeLink(
					this.payroll.processedEmployee?.id || ''
				);
			}
			return '';
		},
		employeeName() {
			return this.payroll.processedEmployee?.name || '';
		},
		orderLink() {
			if (this.listVariant) {
				return this.getOrderLink(this.payroll.order_id || '');
			}
			return '';
		},
		statusIcon() {
			const icon = this.payroll.is_processed
				? 'processed'
				: 'unprocessed';
			return `${this.svgSpritePath}#${icon}`;
		},
		statusText() {
			return this.payroll.is_processed
				? 'Processed'
				: 'Waiting to be processed';
		},
		processedAt() {
			return this.payroll.processed_at
				? new DateService(this.payroll.processed_at).format({
						preset: 'payroll',
				  })
				: '';
		},
		orderDate() {
			return this.getOrderDate();
		},
		margin() {
			const total =
				this.cashCollected +
				Number(this.payroll.zelle) +
				Number(this.payroll.credit_card);
			const result = ((this.sumCashPaid + this.sumCCPaid) / total) * 100;

			return total > 0 ? Math.round(result) : 0;
		},
		marginCash() {
			return this.sumCashPaid + this.sumCCPaid;
		},
		sumCashPaid() {
			return this.items.reduce((acc, item) => acc + item.cash_paid, 0);
		},
		sumCCPaid() {
			return this.items.reduce((acc, item) => acc + item.cc_due_paid, 0);
		},
		cashOnHands() {
			return this.payroll.cash_on_hands_result || 0;
		},
		markAsLabel() {
			return this.payroll.is_processed
				? 'Mark as unprocessed'
				: 'Mark as processed';
		},
		tableColumnWidths() {
			return {
				employee: this.listVariant ? '420px' : '200px',
				hours: this.listVariant ? '163px' : '96px',
				rate: this.listVariant ? '163px' : '131px',
				subtotal: this.listVariant ? '163px' : '131px',
				extras: this.listVariant ? '163px' : '100px',
				cashPaid: this.listVariant ? '163px' : '131px',
				ccPaid: this.listVariant ? '163px' : '131px',
			};
		},
	},
	methods: {
		formatNumber(num) {
			return Number(num).toLocaleString('en-US', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			});
		},
		getCorrespondingSumPaidLabel(item) {
			return item.paymentType === 'cc_due' ? 'CC due, $' : 'Cash paid, $';
		},
		getCorrespondingSumPaidValue(item) {
			return this.formatNumber(
				item.paymentType === 'cc_due'
					? item.cc_due_paid
					: item.cash_paid
			);
		},
		getEmployeeLink(id = '') {
			return id ? `/company/employees/${id}` : '';
		},
		getOrderLink(id = '') {
			return id ? `/orders/${id}` : '';
		},
		getOrderDate() {
			const start = moment(this.payroll.start_at).utc();
			const end = moment(this.payroll.end_at).utc();

			if (start.isSame(end, 'day')) {
				return `${start.format('MMM DD, YYYY')} (${start.format(
					'hh:mm A'
				)} - ${end.format('hh:mm A')})`;
			}

			return `${start.format('MMM DD, YYYY hh:mm A')} - ${end.format(
				'MMM DD, YYYY hh:mm A'
			)}`;
		},
		getCollapseIcon(type) {
			const icon = type === 'collapse' ? 'collapse' : 'expand';
			return `${this.svgSpritePath}#${icon}`;
		},
		openEditing() {
			this.isEditing = true;
			this.$nextTick(() => this.maskInputs());
		},
		formatNumberToHours(num) {
			const hours = Math.floor(num);
			const minutes = Math.round((num - hours) * 60);
			return `${hours}:${minutes.toString().padStart(2, '0')}`;
		},
		formatHoursToNumber(timeStr) {
			if (!timeStr || typeof timeStr !== 'string') return 0;

			const [hours, minutes] = timeStr
				.split(':')
				.map((item) => (isNaN(item) ? 0 : Number(item)));
			return Number((hours + minutes / 60).toFixed(2));
		},
		scrollToPanel() {
			this.$nextTick(() => {
				const panel = this.$refs.payrollPanel;
				const headerHeight =
					document.querySelector('.page-header')?.offsetHeight || 0;
				const tabsHeight =
					document.querySelector('#order-tabs')?.offsetHeight || 0;

				if (panel) {
					const panelTop =
						panel.getBoundingClientRect().top + window.scrollY;
					window.scrollTo({
						top: panelTop - headerHeight - tabsHeight,
						behavior: 'instant',
					});
				}
			});
		},
		async toggleProcessing() {
			this.isLoading = true;

			try {
				const res = await axios.post(
					`/orders/payroll/${this.payroll.id}/toggle-process`
				);

				if (res.data.success) {
					this.$store.commit('order/setPayroll', res.data.record);
				} else {
					console.error(res);
				}

				this.isLoading = false;
			} catch (error) {
				console.error('Ошибка запроса:', error);
				this.isLoading = false;
			}
		},
		async submit() {
			this.isLoading = true;

			try {
				const res = await axios.post(
					`/orders/payroll/${this.payroll.id}`,
					{
						cash_collecte: this.cashCollected || 0,
						items: this.items.map((item) => ({
							employee_id: item.employee_id,
							role_id: item.role_id,
							hours: this.formatHoursToNumber(item.hours),
							hourly_rate: item.hourly_rate || 0,
							extras: item.extras || 0,
							is_cc_due: item.paymentType === 'cc_due',
						})),
					}
				);

				if (res.data.success) {
					this.$store.commit('order/setPayroll', res.data.record);
					this.isEditing = false;
					this.scrollToPanel();
				} else {
					console.error(res);
				}

				this.isLoading = false;
			} catch (error) {
				this.isLoading = false;
				console.error('Ошибка запроса:', error);
			}
		},
		maskInputs() {
			$('[id^="payroll-employee-hours-"]').each(function () {
				Inputmask({
					regex: '^[0-9]{1,2}:[0-5][0-9]$',
					showMaskOnHover: false,
					showMaskOnFocus: false,
				}).mask(this);
			});
		},
	},
};
</script>

<style lang="scss" scoped>
.panel {
	.payroll-payments-content {
		padding-top: 4px;
		padding-bottom: 12px;

		+ .panel-content {
			border-top: 1px solid rgba(0, 0, 0, 0.07);
		}
	}
}

.panel-hdr {
	min-height: 43px;
	padding: 16px 0 12px;
	border-bottom: 0;

	h2 {
		line-height: 0.875rem;

		a {
			color: #6e96d7;
			margin-left: 0;
		}
	}
}

.custom-select {
	min-width: 114px;
	width: auto;
}

.payroll-status {
	--status-color: #e35d6a;
	margin-top: 8px;
	padding-left: 28px;
	display: flex;
	align-items: center;
	gap: 4px;
	font-size: 0.785rem;
	color: var(--color-text-secondary);
	position: relative;

	&--processed {
		--status-color: #479f76;
	}

	&__icon {
		position: absolute;
		left: 0;
		top: 0;
		width: 20px;
		height: 20px;
		border-radius: 4px;
		background: var(--status-color);
		display: flex;
		align-items: center;
		justify-content: center;
	}

	&__text {
		color: var(--status-color);
		font-weight: 500;
	}
}

.value-group {
	display: flex;
	align-items: center;
	padding: 4px 0;

	p {
		margin-bottom: 0;
	}

	> :first-child {
		flex: 0 1 224px;
		font-size: 0.755rem;
		color: var(--color-text-secondary);
	}

	> :last-child:not(input) {
		flex: 1 1 160px;
		color: var(--color-text-primary);
		font-weight: 500;
	}

	> input:last-child {
		flex: 0 1 160px;
	}
}

.text-lg {
	font-size: 1rem;
	font-weight: 700;
}

.employee-block {
	margin-bottom: 24px;
	padding-left: 24px;
	border-left: 1px solid var(--color-text-secondary);

	.value-group {
		min-height: 48px;
	}
}

.employee-name {
	color: #495057;
}

.table {
	td {
		vertical-align: middle;

		&:first-child {
			background-color: #fbfafc;

			p {
				font-size: 0.688rem;
			}
		}
	}
}

.btn-group-sm {
	> .btn-sm {
		padding: 0.3rem 1rem;
		font-size: 0.8125rem;
	}
}

.vertical-divider {
	align-self: stretch;
	margin: 0 24px;
	border-right: 1px solid #dee2e6;
}

.frame-wrap.position-absolute {
	z-index: 2;

	.spinner-border {
		top: 50%;
	}
}

.processed-switcher {
	display: flex;
	align-items: center;
	cursor: pointer;
	padding-left: 0;
	gap: 3.41rem;
	margin: 0 -10px 0 0;

	&.disabled {
		pointer-events: none;
	}
}

.switcher {
	height: 19px;
}

.collapse-btn {
	padding: 0.5rem 0.93rem;

	&.collapsed {
		.less {
			display: none;
		}
	}

	&:not(.collapsed) {
		.more {
			display: none;
		}
	}

	svg {
		width: 13px;
		height: 13px;
		margin-right: 8px;
	}
}

.group-title {
	color: var(--color-text-primary);
	font-weight: 500;
	margin-bottom: 0.5rem;
}
</style>
