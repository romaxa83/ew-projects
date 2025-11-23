<template>
	<div
		class="panel-content position-relative border-faded border-0 mt-0 pt-0 bg-faded"
		style="height: 100%"
	>
		<div
			class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
			:class="{ 'd-none': !loading }"
		>
			<div class="d-flex justify-content-center">
				<div
					class="spinner-border text-info position-absolute"
					style="top: 40%"
					role="status"
				>
					<span class="sr-only">Loading...</span>
				</div>
			</div>
		</div>
		<pinned-notes-container
			v-if="
				$store.getters[
					// TODO: Remove prev version after migration
					this.v2
						? 'order/isShowPinnedNotesV2'
						: 'order/isShowPinnedNotes'
				]
			"
		/>

		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-bottom-0 rounded-top"
		>
			<div>
				<div
					v-if="
						$store.getters[
							// TODO: Remove prev version after migration
							this.v2
								? 'order/pinnedNotesTextV2'
								: 'order/pinnedNotesText'
						]
					"
					class="fa-sm ml-2 pinned-notes-link"
				>
					<a
						href="pinnedNotes"
						@click.prevent="
							$store.commit(
								// TODO: Remove prev version after migration
								this.v2
									? 'order/toggleShowPinnedNotesV2'
									: 'order/toggleShowPinnedNotes'
							)
						"
						>{{
							// TODO: Remove prev version after migration
							$store.getters[
								this.v2
									? 'order/pinnedNotesTextV2'
									: 'order/pinnedNotesText'
							]
						}}</a
					>
				</div>
				<input-interface-dropdown :mode="mode" />
			</div>
			<div class="ml-2 d-flex">
				<div class="custom-control custom-switch pt-2">
					<input
						v-model="form.pinned"
						type="checkbox"
						class="custom-control-input"
						id="note_is_pinned"
					/>
					<label class="custom-control-label" for="note_is_pinned"
						>Pinned</label
					>
				</div>
			</div>
		</div>
		<textarea
			oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'
			v-model="form.text"
			rows="3"
			class="form-control border border-bottom-left-radius-0 border-bottom-right-radius-0 border-top-left-radius-0 border-top-right-radius-0 overflow-hidden"
			placeholder="Type here..."
		></textarea>
		<div
			class="d-flex align-items-center py-2 px-2 bg-white border border-top-0 rounded-bottom"
		>
			<!--            <button type="button" class="btn btn-icon fs-lg waves-effect waves-themed">-->
			<!--                <i class="fal fa-paperclip"></i>-->
			<!--            </button>-->
			<button
				@click="submit"
				type="button"
				class="btn btn-primary btn-sm ml-auto waves-effect waves-themed"
				:disabled="submitDisabled"
			>
				Create Note
			</button>
		</div>
	</div>
</template>

<script>
import { axiosPromise } from '@/helpers/axiosPromise';
import pinnedNotesContainer from '@components/Order/TabOverview/CommunicationPanel/History/PinnedNotesContainer';
import inputInterfaceDropdown from '@components/Order/TabOverview/CommunicationPanel/InputInterfaceDropdown';
import { mapGetters } from 'vuex';

export default {
	name: 'InputInterfaceNote',
	props: ['mode', 'v2'],
	data: () => ({
		loading: false,
		form: {
			pinned: false,
			text: '',
		},
	}),
	computed: {
		// orderID() {
		//     return this.$store.state.session?.order?.id;
		// },
		submitDisabled() {
			if (this.form.text.trim().length < 1) return true;
			return false;
		},
		...mapGetters({
			pinnedNotes: 'order/getOrderPinnedNotes',
			pinnedNotesV: 'order/getOrderPinnedNotes2',
			orderID: 'getOrderId',
			// users: 'appTasks/users',
			// statuses: 'appTasks/statuses',
			// types: 'appTasks/types',
			// whoami: 'appTasks/whoami',
			// activeTypes: 'appTasks/activeTypes',
			// activeUsers: 'appTasks/activeUsers',
		}),
	},
	methods: {
		submit() {
			axiosPromise(
				axios.post('/orders/notes/save', {
					order_id: this.orderID,
					text: this.form.text,
					is_pinned: this.form.pinned,
					returnFormat: 'communicationPanel',
				})
			)
				.then(({ record }) => {
					this.$store.commit(
						this.v2
							? 'order/pushCommunicationRecordV2'
							: 'order/pushCommunicationRecord',
						record
					);
					this.form.pinned = false;
					this.form.text = '';
					//console.log(data);
					// App.Forms.showAlert('success', 'Note created');
					// reload history
				})
				.then(() => {
					this.$store.dispatch('order/refetchChangelog', 'update');
				})
				.catch((error) => {
					console.log(error);
					App.Forms.simpleErrors(error);
				});

			// axios
			//     .post('/orders/notes/save', {
			//         order_id: orderID,
			//         text: this.form.text,
			//         is_pinned: this.is_pinned,
			//     })
			//     .then(resp => {
			//         if (resp.data.success === true) {
			//             // this.$store.dispatch('order/updateNotes', resp.data.records);
			//             // this.is_pinned = true;
			//             this.form.text = '';
			//         } else {
			//             App.Forms.simpleErrors(resp.data);
			//         }
			//     })
			//     .catch(error => {
			//         App.Forms.simpleErrors(error.response.data);
			//     })
			//     .finally(() => (this.loading = false));
		},
	},
	components: {
		inputInterfaceDropdown,
		pinnedNotesContainer,
	},
};
</script>
