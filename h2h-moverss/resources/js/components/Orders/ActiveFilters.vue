<template>
	<div class="selected-filters-container mb-3">
		<div class="text-nowrap mb-1">
			<button
				v-if="
					filters['daterange-type'] &&
					filters['daterange-type'] !== 'by-none'
				"
				type="button"
				class="btn btn-xs btn-white waves-effect waves-themed mr-2"
				@click="uncheckAllInSection('daterange')"
			>
				<i class="fal fa-times"></i> {{ rangeBy }}: {{ rangeStart }} -
				{{ rangeEnd }}
			</button>

			<template v-if="filters.stage">
				<button
					v-if="filters.stage.length >= 4"
					type="button"
					class="btn btn-xs btn-white waves-effect waves-themed mr-2"
					@click="uncheckAllInSection('stage')"
				>
					<i class="fal fa-times"></i> Stage:
					{{ filters.stage.length }} selected
				</button>
				<template v-else>
					<button
						v-for="stage_id in filters.stage"
						:key="stage_id"
						type="button"
						class="btn btn-xs btn-white waves-effect waves-themed mr-2"
						@click="uncheckByKey('stage', stage_id)"
					>
						<i class="fal fa-times"></i> Stage:
						{{ params.stages[stage_id].title }}
					</button>
				</template>
			</template>

			<!--                ORDER ID, myLeads, newLeads -->
			<!--                <template v-if="filters.client">-->
			<!--                    <button v-for="client_id in filters.client" :key="client_id" type="button" class="btn btn-xs btn-white waves-effect waves-themed mr-2">-->
			<!--                        <i class="fal fa-times"></i> Client: {{ params.filteredClients[client_id].name }} {{ params.filteredClients[client_id].lname }}-->
			<!--                    </button>-->
			<!--                </template>-->

			<i v-for="(f, k) in filters.filter" :key="k">
				<button
					v-if="k === 'tasks'"
					type="button"
					class="btn btn-xs btn-white waves-effect waves-themed mr-2"
					:class="{ 'd-none': !f }"
					@click="uncheckAllInSection(k)"
				>
					<i class="fal fa-times"></i>
					{{ titleBySection(k) }}: {{ valueBySection(k, f) }}
				</button>
				<button
					v-else-if="k === 'estimate'"
					type="button"
					class="btn btn-xs btn-white waves-effect waves-themed mr-2"
					:class="{ 'd-none': !f.min && !f.max }"
					@click="uncheckAllInSection(k)"
				>
					<i class="fal fa-times"></i>
					{{ titleBySection(k) }}
					<span v-show="f.min">min: {{ f.min }}</span>
					<span v-if="f.min && f.max"> - </span>
					<span v-show="f.max">max: {{ f.max }}</span>
				</button>
				<template v-else-if="k === 'source' || k === 'manager'">
					<button
						v-for="(vv, kk) in f"
						:key="k + kk"
						type="button"
						class="btn btn-xs btn-white waves-effect waves-themed mr-2"
						@click="uncheckByKey(k, vv)"
					>
						<i class="fal fa-times"></i>
						{{ titleBySection(k) }}: {{ valueBySection(k, vv) }}
					</button>
				</template>
				<template v-else-if="k === 'clientTags' || k === 'orderTags'">
					<button
						v-for="(vv, kk) in f"
						:key="k + kk"
						type="button"
						class="btn btn-xs btn-white waves-effect waves-themed mr-2"
						@click="uncheckByKey(k, vv)"
					>
						<i class="fal fa-times"></i>
						{{ titleBySection(k) }}: {{ valueBySection(k, vv) }}
					</button>
				</template>
				<template v-else>
					<button
						v-for="(vv, kk) in f"
						:key="k + kk"
						type="button"
						class="btn btn-xs btn-white waves-effect waves-themed mr-2"
						@click="uncheckByKey(k, kk)"
					>
						<i class="fal fa-times"></i>
						{{ titleBySection(k) }}: {{ valueBySection(k, kk) }}
						<!--                        <i>debug: k2: {{ k }} - f: {{ f }}</i>-->
					</button>
				</template>
			</i>
		</div>
	</div>
</template>
<script>
export default {
	name: 'OrdersActiveFilters',
	data() {
		return {
			params: window.params,
			filters: {},
		};
	},
	computed: {
		rangeBy() {
			const type = this.filters['daterange-type'];

			return this.params['daterange-type'].hasOwnProperty(type)
				? this.params['daterange-type'][type]
				: 'n/a';
		},
		rangeEnd() {
			return moment(this.filters['date-range'].end, 'YYYY-MM-DD').format(
				'MMM DD, YYYY'
			);
		},
		rangeStart() {
			return moment(
				this.filters['date-range'].start,
				'YYYY-MM-DD'
			).format('MMM DD, YYYY');
		},
	},
	mounted() {
		window.addEventListener('load', () => {
			App.OrderList.init();
		});
	},
	methods: {
		passData(filters) {
			this.filters = filters;
			// console.log('passData', filters);
			// console.log('params', window.params);
		},
		titleBySection(section) {
			let name = section.charAt(0).toUpperCase() + section.slice(1);

			if (section === 'move-type') {
				name = 'Move type';
			} else if (section === 'move-size') {
				name = 'Move size';
			} else if (section === 'works') {
				name = 'Services';
			} else if (section === 'client-tags') {
				name = 'Client Tags';
			} else if (section === 'order-tags') {
				name = 'Order Tags';
			}

			return name;
		},
		uncheckAllInSection(key) {
			let content = $('#filter-btn').tooltipster('content')[0];

			if (key === 'tasks') {
				$('#filters-order-tasks select', content).prop(
					'selectedIndex',
					0
				);
			} else if (key === 'daterange') {
				$('#daterangepicker').prop('disabled', true);
				$('#daterange-type').prop('selectedIndex', 0);
			} else if (key === 'estimate') {
				$('#filters-pills-estimate input', content).val('');
			} else {
				$(`#filters-pills-${key} [type="checkbox"]`, content).prop(
					'checked',
					false
				);
			}

			$('#DT-Order-List').DataTable().ajax.reload();
		},
		uncheckByKey(section, key) {
			let content = $('#filter-btn').tooltipster('content')[0];

			// console.log(`uncheckByKey #checkbox-${section}-${key}`);
			$(`#checkbox-${section}-${key}`, content).prop('checked', false);

			if (section === 'source' || section === 'manager') {
				$(`#select-${section} option[value="${key}"]`, content).prop(
					'selected',
					false
				);

				$(`#select-${section}`, content).trigger('change');
			}

			$('#DT-Order-List').DataTable().ajax.reload();
		},
		valueBySection(section, val) {
			let title = `n/a s: ${section} v: ${val}`;

			if (section === 'tasks' && this.params[section][val])
				title = this.params[section][val];
			else if (this.params[section][val])
				title =
					this.params[section][val].title ??
					this.params[section][val].name ??
					'not found name in params o_0';

			return title;
		},
	},
};
</script>
