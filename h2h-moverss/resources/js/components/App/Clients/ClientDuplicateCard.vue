<template>
	<div class="card border mb-g mr-2 duplicate-card">
		<!-- notice the additions of utility paddings and display properties on .card-header -->
		<div
			class="card-header bg-gray-300 pr-3 d-flex align-items-center flex-wrap"
		>
			<!-- we wrap header title inside a span tag with utility padding -->
			<div class="card-title">Client #{{ client.id }}</div>
			<!--                            <button class="btn btn-icon btn-md btn-danger ml-auto fs-xl waves-effect waves-themed" data-toggle="dropdown" aria-expanded="false">-->
			<!--                                <i class="fas fa-external-link"></i>-->
			<!--                            </button>-->
		</div>
		<div class="card-body">
			<div class="ml-1">
				<a href="selectAll" @click.prevent="makeSelectAll"
					>Select all</a
				>
			</div>
			<!-- name -->
			<div
				class="d-flex border border-top-0 border-right-0 border-left-0 py-3 pl-2"
			>
				<div class="mr-1">
					<b-form-checkbox
						:value="{
							client_id: client.id,
							text: client.name + ' ' + client.lname,
						}"
						v-model="checkboxes.name"
					>
						<h5 class="mb-0 text-dark fw-500">
							{{ client.name }} {{ client.lname }}
						</h5>
					</b-form-checkbox>
				</div>
			</div>
			<!-- phones -->
			<div
				v-for="v in client.phones"
				:key="v.id"
				class="d-flex border border-top-0 border-right-0 border-left-0 py-3 pl-2"
			>
				<b-form-checkbox-group v-model="checkboxes.phones">
					<div class="mr-1">
						<b-form-checkbox :value="{ id: v.id, text: v.value }">
							{{ v.value | formatPhone }}
						</b-form-checkbox>
					</div>
					<!--                <div>-->
					<!--                    {{ v.value | formatPhone }}-->
					<!--                </div>-->
				</b-form-checkbox-group>
			</div>
			<!-- emails -->
			<div
				v-for="v in client.emails"
				:key="v.id"
				class="d-flex border border-top-0 border-right-0 border-left-0 py-3 pl-2"
			>
				<b-form-checkbox-group v-model="checkboxes.emails">
					<div class="mr-1">
						<b-form-checkbox :value="{ id: v.id, text: v.value }">
							{{ v.value }}
						</b-form-checkbox>
					</div>
				</b-form-checkbox-group>
			</div>
			<!-- messengers -->
			<div
				v-for="v in client.messengers"
				:key="v.id"
				class="d-flex border border-top-0 border-right-0 border-left-0 py-3 pl-2"
			>
				<b-form-checkbox-group v-model="checkboxes.messengers">
					<div class="mr-1">
						<b-form-checkbox :value="{ id: v.id, text: v.value }">
							{{ v.value }}
						</b-form-checkbox>
					</div>
				</b-form-checkbox-group>
			</div>
			<!-- notes -->
			<div
				v-for="v in client.notes"
				:key="v.id"
				class="d-flex border border-top-0 border-right-0 border-left-0 py-3 pl-2"
			>
				<b-form-checkbox-group v-model="checkboxes.notes">
					<b-form-checkbox :value="{ id: v.id, text: v.value }">
						<div class="p-1 flex-fill">
							<div class="panel-tag fs-xs position-relative">
								<p class="mb-0 mt-2">{{ v.value }}</p>
							</div>
						</div>
					</b-form-checkbox>
				</b-form-checkbox-group>
				<!--                <div class="mr-1">-->
				<!--                    <b-form-checkbox-->
				<!--                    >-->
				<!--                    </b-form-checkbox>-->
				<!--                </div>-->
				<!--                <div class="p-1 flex-fill">-->
				<!--                    <div class="panel-tag fs-xs position-relative">-->
				<!--                        <p class="mb-0 mt-2">{{ v.value }}</p>-->
				<!--                    </div>-->
				<!--                </div>-->
			</div>
			<!-- tags -->
			<div
				v-if="client.tags.length"
				class="d-flex border border-top-0 border-right-0 border-left-0 py-3 pl-2"
			>
				<b-form-checkbox-group v-model="checkboxes.tags">
					<b-form-checkbox
						:value="{ client_id: client.id, value: client.tags }"
						inline
					>
						<client-tags
							class="mt-2"
							v-if="client.tags"
							:tags="client.tags"
						></client-tags>
					</b-form-checkbox>
				</b-form-checkbox-group>
			</div>

			<div class="py-3 pl-2">
				<a
					target="_blank"
					:href="`/orders?filter-client[]=` + client.id"
					class="btn btn-default waves-effect waves-themed"
				>
					Orders
					<span class="badge bg-primary-500 ml-2">{{
						client.orders_count
					}}</span></a
				>
			</div>
		</div>
	</div>
</template>

<script>
import formatPhone from '@/filters/formatPhone.filter';
import ClientTags from '@components/Order/TabOverview/Client/ClientTags'; // import formatDate from "@/filters/formatDate.filter";
import { BFormCheckbox, BFormCheckboxGroup } from 'bootstrap-vue'; // import formatDate from "@/filters/formatDate.filter";

// import formatDate from "@/filters/formatDate.filter";
// import managerName from "@/filters/managerName.filter";
export default {
	name: 'ClientDuplicateCard',
	props: ['client', 'checkboxes'],
	data: () => {
		return {
			id: null,
			// checkbox: false,
			selectAll: {},
		};
	},
	filters: {
		// formatDate,
		// managerName,
		formatPhone,
	},
	computed: {
		// client() {
		//     return this.clientCard;
		// }
	},
	updated() {
		this.id = this._uid;
		this.fillSelectAll();
	},
	mounted() {
		this.id = this._uid;
		this.fillSelectAll();
	},
	methods: {
		makeSelectAll() {
			this.$emit('select-all', this.selectAll);
		},
		fillSelectAll() {
			this.selectAll['name'] = {
				client_id: this.client.id,
				text: this.client.name + ' ' + this.client.lname,
			};
			if (this.client.phones.length) {
				this.selectAll['phones'] = this.client.phones.map((v) => ({
					id: v.id,
					text: v.value,
				}));
			} else {
				this.selectAll['phones'] = [];
			}
			if (this.client.emails.length) {
				this.selectAll['emails'] = this.client.emails.map((v) => ({
					id: v.id,
					text: v.value,
				}));
			} else {
				this.selectAll['emails'] = [];
			}
			if (this.client.messengers.length) {
				this.selectAll['messengers'] = this.client.messengers.map(
					(v) => ({
						id: v.id,
						text: v.value,
					})
				);
			} else {
				this.selectAll['messengers'] = [];
			}
			if (this.client.notes.length) {
				this.selectAll['notes'] = this.client.notes.map((v) => ({
					id: v.id,
					text: v.value,
				}));
			} else {
				this.selectAll['notes'] = [];
			}
			if (this.client.tags) {
				this.selectAll['tags'] = [
					{
						client_id: this.client.id,
						value: this.client.tags,
					},
				];
			} else {
				this.selectAll['tags'] = [];
			}
		},
	},
	components: {
		BFormCheckboxGroup,
		BFormCheckbox,
		ClientTags,
	},
};
</script>

<style scoped>
.duplicate-card {
	width: 300px;
	min-width: 300px;
}
</style>
