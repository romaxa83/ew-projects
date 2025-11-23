<template>
	<div class="notes-popover">
		<h4 v-if="inLine">Notes:</h4>

		<div v-for="(v, i) in records" :key="`notes-${i}`">
			<div class="d-flex justify-content-between">
				<div class="fs-sm mb-1">
					<span v-if="v.id">
						{{ v.created_at | formatDate('ll, [at] h:mm a') }} by
						{{ v.user_id | managerName }}
					</span>
					<span v-else> Not saved yet, by You </span>
				</div>
				<div class="position-absolute pos-right mt-n2">
					<div class="dropleft">
						<button
							type="button"
							class="btn btn-toolbar-master waves-effect waves-themed"
							data-toggle="dropdown"
							aria-haspopup="true"
							aria-expanded="false"
						>
							<i class="fal fa-ellipsis-v"></i>
						</button>
						<div class="dropdown-menu">
							<button
								v-if="hasEdit"
								class="dropdown-item"
								type="button"
								@click="toggleEditRecord(i)"
							>
								<i class="fal fa-pencil mr-2"></i>
								<span v-if="editingIds.includes(i)"
									>Cancel Edit</span
								>
								<span v-else>Edit</span>
							</button>
							<button
								class="dropdown-item"
								type="button"
								@click="deleteRecord(i)"
							>
								<i class="fal fa-times mr-2"></i> Delete
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-tag mb-3">
				<div v-if="editingIds.includes(i)">
					<textarea
						oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'
						v-model="v.value"
						rows="1"
						class="fs-sm form-control py-0 px-0 border-0 overflow-hidden"
						placeholder="Type here..."
					></textarea>

					<button
						@click="toggleEditRecord(i, true)"
						type="button"
						class="mt-3 btn btn-xs btn-info waves-effect waves-themed"
					>
						Save
					</button>
				</div>
				<span v-else v-text="v.value"></span>
			</div>
		</div>

		<div class="d-none">
			<div id="popover_content" style="width: 450px">
				<h4 class="fw-500 width-sm">
					<i class="fal fa-file-check mr-2"></i>Create note
				</h4>
				<div class="form-group">
					<textarea
						class="form-control"
						rows="3"
						placeholder="New note"
						v-model="new_comment"
                        ref="textarea"
					></textarea>

					<button
						@click="hideAndAdd"
						type="button"
						class="mt-3 btn btn-xs btn-info waves-effect waves-themed"
					>
						Save
					</button>
				</div>
			</div>
		</div>

		<button
			v-show="inLine"
			type="button"
			class="btn btn-xs btn-default waves-effect waves-themed mb-2 createNote"
			data-toggle="popover"
		>
			<span class="fal fa-check mr-1"></span>
			Add new
		</button>
	</div>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';

let popover;
export default {
	name: 'ModalNotes',
	filters: {
		formatDate,
		managerName,
	},
	props: {
		hasEdit: {
			type: Boolean,
			default: false,
			required: false,
		},
		ignoreEmpty: {
			type: Boolean,
			default: false,
		},
		inLine: {
			type: Boolean,
			default: true,
		},
		records: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			new_comment: null,
			editingIds: [],
			textBackups: [],
		};
	},
	mounted() {
		let vm = this;
		popover = $('.createNote')
			.popover({
				content: $('#popover_content'),
				html: true,
			})
			.on('show.bs.popover', function () {
				vm.new_comment = null;

				$($(this).data('bs.popover').getTipElement()).css(
					'max-width',
					'500px'
				);

				// Close modal - on click outside
				$('html').on('mouseup', function (e) {
					if (
						$(e.target).hasClass('popover') ||
						$(e.target).closest('.popover').length
					) {
						return;
					}

					$('[data-toggle="popover"]').popover('hide');
				});
			})
            .on('shown.bs.popover', () => {
                this.$refs.textarea.focus();
            });
	},
	methods: {
		addRecord() {
			if (!this.new_comment) {
				if (!this.ignoreEmpty) {
					App.Forms.showAlert(
						'warning',
						'Error',
						'Note text is empty'
					);
				}
				return;
			}
			this.$emit('addRecord', {
				id: null,
				value: this.new_comment,
			});
			this.new_comment = null;
			popover.popover('hide');
		},
		deleteRecord(index) {
			this.$emit('deleteRecord', index);
		},
		focusHandler(event) {
			event.stopImmediatePropagation();
		},
		hideAndAdd() {
			$('[data-toggle="popover"]').popover('hide');
			if (this.new_comment) this.addRecord();
		},
		resetEdit() {
			this.editingIds = [];
			this.textBackups = [];
		},
		toggleEditRecord(i, is_save = false) {
			if (is_save) {
				// save edit
				let editIndex = this.editingIds.indexOf(i);

				this.$delete(this.editingIds, editIndex);
				this.$delete(this.textBackups, editIndex);
			} else if (this.editingIds.includes(i)) {
				// cancel edit
				let editIndex = this.editingIds.indexOf(i);

				this.records[i].value = this.textBackups[editIndex]; // revert value

				this.$delete(this.editingIds, editIndex);
				this.$delete(this.textBackups, editIndex);
			} else {
				// enable edit
				this.editingIds.push(i);
				this.textBackups.push(this.records[i].value);
			}
			this.$emit('closeDrop');
		},
	},
};
</script>
