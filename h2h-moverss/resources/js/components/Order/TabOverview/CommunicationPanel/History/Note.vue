<template>
	<li>
		<div
			class="frame-wrap position-absolute w-100 h-100 opacity-60 panel-loader"
			:class="{ 'd-none': !loading }"
		>
			<div class="d-flex justify-content-center">
				<div
					class="spinner-border text-info position-absolute"
					style="top: 30%"
					role="status"
				>
					<span class="sr-only">Loading...</span>
				</div>
			</div>
		</div>

		<!--        <button class="btn btn-lg btn-white btn-icon activity-timeline-icon rounded-circle js-waves-off">-->
		<!--            <i title="messages"-->
		<!--               class="fal fa-2x fa-file-signature"></i>-->
		<!--        </button>-->
		<div
			class="card card-zoom-hover position-relative overflow-hidden"
			:class="{ 'border border-primary': item.is_pinned }"
			@mouseenter="isHovered = true"
			@mouseleave="isHovered = false"
		>
			<div
				v-if="isHovered && !editMode && isAllowedControl"
				class="bg-danger-50 position-absolute h-100 pos-right d-flex align-items-center control-block"
			>
				<div class="ml-auto mr-auto">
					<button
						@click="enterEditMode()"
						class="btn btn-secondary waves-effect waves-themed mr-3"
					>
						Edit
					</button>
					<button
						@click="removeNote()"
						class="btn btn-danger waves-effect waves-themed"
					>
						Delete
					</button>
				</div>
			</div>
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
				:class="[
					item.is_pinned ? 'bg-primary-300' : 'bg-white text-white',
				]"
			>
				<div class="mr-1 text-muted">
					<i class="fas fa-file-signature"></i>
				</div>
				<div
					class="fs-xs mr-auto pr-3"
					:class="[item.is_pinned ? '' : 'text-muted']"
				>
					<i v-if="item.is_pinned" class="fas fa-map-pin mr-2"></i>
					<b>Note</b> by {{ author }}
				</div>
				<div
					class="d-flex position-relative pr-2 fs-xs"
					:class="[item.is_pinned ? '' : 'text-muted']"
				>
					{{ datetime }}
				</div>
			</div>
			<div
				v-if="!editMode"
				class="card-body fs-sm py-2"
				v-html="item.text"
			></div>
			<div v-if="editMode" class="card-body fs-sm py-2">
				<textarea
					oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'
					v-model="editText"
					rows="1"
					class="fs-sm form-control py-0 px-0 border-0 overflow-hidden"
					placeholder="Type here..."
				></textarea>
			</div>
			<div class="card-footer text-muted py-2" v-show="editMode">
				<div class="d-flex align-items-center">
					<button
						@click="modifyNote()"
						class="btn btn-sm btn-success waves-effect waves-themed mr-2"
						:disabled="!changed"
					>
						Save
					</button>
					<button
						@click="toggleEditMode()"
						class="btn btn-sm btn-default waves-effect waves-themed"
					>
						Cancel
					</button>
				</div>
			</div>
		</div>
	</li>
</template>

<script>
export default {
	name: 'Note',
	props: ['record', 'datetime'],
	data: () => ({
		loading: false,
		controlHovered: false,
		isHovered: false,
		editText: '',
		editMode: false,
	}),
	// mounted() {
	//     this.editText = this.item.text;
	// },
	computed: {
		changed() {
			if (this.editText.length < 2) return false;
			return this.editText != this.item.text;
		},
		isAllowedControl() {
			if (
				this.whoami &&
				(this.whoami.is_admin || this.whoami.uid == this.item.user_id)
			)
				return true;
			return false;
		},
		whoami() {
			return this.$store.state.tasksCalendar.whoami;
		},
		item() {
			return this.record.item;
		},
		author() {
			if (this.item?.author?.employee)
				return (
					this.item.author.employee.name +
					' ' +
					this.item.author.employee.l_name
				);
			else if (this.item?.author?.name) return this.item?.author.name;
			return '';
		},
	},
	methods: {
		modifyNote() {
			this.loading = true;
			this.$store
				.dispatch('order/updateOrderNote', {
					id: this.item.id,
					text: this.editText,
				})
				.then(({ record }) => {
					this.$store.commit(
						'order/updateCommunicationRecord',
						record
					);
					this.editMode = false;
					this.loading = false;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},

		removeNote() {
			this.loading = true;
			this.$store
				.dispatch('order/removeOrderNote', { id: this.item.id })
				.then(() => {
					this.$store.commit('order/removeCommunicationRecord', {
						type: 'Notes',
						id: this.item.id,
					});
					// this.loading = false;
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
				});
		},
		enterEditMode() {
			this.editText = this.item.text;
			this.toggleEditMode();
		},
		toggleEditMode() {
			this.editMode = !this.editMode;
		},
	},
};
</script>

<style scoped>
.control-block {
	z-index: 3;
	width: 230px;
	right: -215px;
	cursor: pointer;
	transition: 0.5s;
}

.control-block:hover {
	right: 0px;
	cursor: default;
}

/*.expanded {*/
/*    transition: all 0.2s linear;*/
/*    width: 250px;*/
/*    z-index: 3;*/
/*}*/

/*.collapsed {*/
/*    !*animation: 0.5s linear;*!*/
/*    cursor: pointer;*/
/*    width: 15px;*/
/*    z-index: 3;*/
/*    opacity: .5;*/
/*}*/
</style>
