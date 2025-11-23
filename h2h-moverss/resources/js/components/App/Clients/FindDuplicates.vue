<template>
	<div>
		<button
			type="button"
			class="btn text-nowrap btn-primary waves-effect waves-themed"
			@click="findDuplicates"
		>
			Find duplicates
		</button>

		<b-modal
			id="duplicates-modal"
			ref="duplicates-modal"
			hide-backdrop
			size="xl"
			centered
			body-bg-variant="gray-100"
			header-bg-variant="gray-100"
			footer-class="d-none"
		>
			<template #modal-header="{ close }">
				<div class="d-flex flex-fill">
					<div class="mr-auto">
						<h4>
							Find & merge duplicates
							{{ currentDuplicatesCount }}/{{ total }}
						</h4>
					</div>
					<div>
						<b-overlay :show="busy">
							<template #overlay>
								<div></div>
							</template>
							<button
								@click="close()"
								class="btn btn-sm btn-default"
							>
								Cancel
							</button>
							<button
								class="btn btn-sm btn-warning ml-1"
								@click="makeSkip()"
							>
								Skip this duplicate
							</button>
							<button
								class="btn btn-sm btn-primary ml-1"
								@click="makeMerge"
							>
								Merge this duplicate
							</button>
						</b-overlay>
					</div>
				</div>
			</template>
			<b-overlay :show="busy" rounded="sm">
				<div class="mb-2">
					<b>Be careful, merge cannot be revert!</b><br />Search going
					only by customers who has orders with branch, available to
					you
				</div>
				<div class="d-flex">
					<div class="card border mr-2">
						<div
							class="card-body d-flex"
							style="overflow-x: scroll"
							width="750"
						>
							<client-duplicate-card
								v-for="(clientCard, k) of duplicates"
								:client="clientCard"
								:key="k"
								:checkboxes.sync="checkboxes"
								@select-all="makeSelect"
							/>
						</div>
					</div>
					<div class="mt-3">
						<div class="card mb-g mr-2" style="width: 300px">
							<!-- notice the additions of utility paddings and display properties on .card-header -->
							<div
								class="card-header bg-gray-100 border-right-0 pr-3 d-flex align-items-center flex-wrap"
							>
								<!-- we wrap header title inside a span tag with utility padding -->
								<div class="card-title">Merged info</div>
								<!--                            <button class="btn btn-icon btn-md btn-danger ml-auto fs-xl waves-effect waves-themed" data-toggle="dropdown" aria-expanded="false">-->
								<!--                                <i class="fas fa-external-link"></i>-->
								<!--                            </button>-->
							</div>
							<div class="card-body bg-gray-100">
								<div class="form-group mb-2">
									<label class="form-label text-muted fs-nano"
										>name</label
									>
									<h5 class="mb-0 text-dark fw-500">
										{{ mergedName }}
									</h5>
								</div>
								<div class="form-group mb-2">
									<label class="form-label text-muted fs-nano"
										>phones</label
									>
									<div v-for="phone of mergedPhones">
										{{ phone | formatPhone }}
									</div>
								</div>
								<div class="form-group mb-2">
									<label class="form-label text-muted fs-nano"
										>emails</label
									>
									<div v-for="(email, k) of mergedEmails">
										{{ email }}
									</div>
								</div>
								<div class="form-group mb-2">
									<label class="form-label text-muted fs-nano"
										>notes</label
									>
									<div v-for="(note, k) of mergedNotes">
										<div class="p-1 flex-fill">
											<div
												class="panel-tag fs-xs position-relative"
											>
												<p class="mb-0 mt-2">
													{{ note }}
												</p>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group mb-2">
									<label class="form-label text-muted fs-nano"
										>tags</label
									>
									<div v-if="mergedTags.length">
										<client-tags
											class="mt-2"
											:tags="mergedTags"
										></client-tags>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--                <div class="col">-->
					<!--                </div>-->
				</div>
			</b-overlay>
		</b-modal>
	</div>
</template>

<script>
import formatPhone from '@/filters/formatPhone.filter';
import { AxiosHelper } from '@/helpers/axiosHelper';
import ClientDuplicateCard from '@components/App/Clients/ClientDuplicateCard.vue';
import ClientTags from '@components/Order/TabOverview/Client/ClientTags';
import { BModal, BOverlay, VBModal } from 'bootstrap-vue';

export default {
	name: 'FindDuplicates',
	props: ['action'],
	components: {
		BOverlay,
		ClientDuplicateCard,
		BModal,
		ClientTags,
	},
	filters: {
		// formatDate,
		// managerName,
		formatPhone,
	},
	computed: {
		mergedName() {
			if (this.checkboxes.name) {
				return this.checkboxes.name.text;
			}
			return '';
		},
		mergedPhones() {
			if (this.checkboxes.phones.length) {
				return [...new Set(this.checkboxes.phones.map((v) => v.text))];
			}
			return [];
		},
		mergedEmails() {
			if (this.checkboxes.emails.length) {
				return [...new Set(this.checkboxes.emails.map((v) => v.text))];
			}
			return [];
		},
		mergedTags() {
			if (this.checkboxes.tags.length) {
				return this.checkboxes.tags.reduce(function (prev, curr) {
					return [...prev, ...curr.value];
				}, []);
			}
			return [];
		},
		mergedNotes() {
			if (this.checkboxes.notes.length) {
				return this.checkboxes.notes.map((v) => v.text);
			}

			return [];
		},
		currentDuplicatesCount() {
			return this.duplicates ? this.duplicates.length : 0;
		},
	},
	data: () => {
		return {
			busy: false,
			duplicates: [],
			currentDuplicate: {},
			skip: {
				phones: [],
				emails: [],
			},
			checkboxes: {
				name: false,
				phones: [],
				emails: [],
				messengers: [],
				notes: [],
				tags: [],
			},
			tags: [],
			total: null,
		};
	},
	directives: {
		'b-modal': VBModal,
	},
	mounted() {
		// this.$refs['duplicates-modal'].show()
	},
	methods: {
		makeSelect(payload) {
			this.checkboxes = { ...this.checkboxes, ...payload };
		},
		resetCheckboxes() {
			// console.log('resetCheckboxed!');
			this.checkboxes = {
				name: false,
				phones: [],
				emails: [],
				messengers: [],
				notes: [],
				tags: [],
			};
		},
		makeMerge() {
			$('#content-spinner').toggleClass('d-none');
			this.busy = true;
			return AxiosHelper({
				url: '/clients/mergeDuplicates',
				data: {
					duplicates: this.duplicates.map((v) => v.id),
					mergeBy: this.checkboxes,
					skip: this.skip,
				},
				// method: "POST",
			})
				.then(({ data }) => {
					this.resetCheckboxes();
					this.currentDuplicate = data.duplicateBy;
					this.duplicates = data.duplicates;
					this.total = data.totalDuplicates;
					$('#content-spinner').toggleClass('d-none');
					this.busy = false;
				})
				.catch(() => {
					$('#content-spinner').toggleClass('d-none');
					this.busy = false;
				});
		},
		makeSkip() {
			this.skip[this.currentDuplicate.relation].push(
				this.currentDuplicate.value
			);
			// this.busy = true;
			this.findDuplicates();
		},
		findDuplicates() {
			this.resetCheckboxes();
			$('#content-spinner').toggleClass('d-none');
			return AxiosHelper({
				url: '/clients/findDuplicates',
				data: { skip: this.skip },
				// method: "POST",
			})
				.then(({ data }) => {
					// console.log({data});
					// this.selected = this.createStore(data.duplicates)
					this.currentDuplicate = data.duplicateBy;
					this.duplicates = data.duplicates;
					this.total = data.totalDuplicates;
					$('#content-spinner').toggleClass('d-none');
					// this.busy = false;
					this.$refs['duplicates-modal'].show();
					// this.accounts = records
					//     .map(item => {
					//         let users = item.users
					//             .map(v => v.id)
					//
					//         item.users = users;
					//
					//         return item
					//     });
				})
				.catch((err) => {});
			// $.ajax({
			//     url: '/clients/findDuplicates',
			//     type: "POST",
			// }).done(function (response) {
			//
			// }).fail(function (jqXHR, textStatus, errorThrown) {
			//
			// });
		},
	},
};
</script>
