<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>New note</h2>
		</div>
		<div class="panel-container">
			<div class="panel-content">
				<textarea
					v-model="text"
					class="form-control"
					rows="3"
					placeholder="Note text"
				></textarea>
				<!--                <div class="row mt-2 mb-3">-->
				<!--                    <div class="col">-->
				<!--                        <div class="form-group">-->
				<!--                            <label class="form-label">-->
				<!--                                Visibility-->
				<!--                            </label>-->
				<!--                            <select class="form-control">-->
				<!--                                <option value="">Internal</option>-->
				<!--                                <option value="">Crew</option>-->
				<!--                                <option value="CA">Customer</option>-->
				<!--                            </select>-->
				<!--                        </div>-->
				<!--                    </div>-->
				<!--                </div>-->

				<div class="d-flex mt-2">
					<div class="custom-control custom-switch pt-2">
						<input
							v-model="is_pinned"
							type="checkbox"
							class="custom-control-input"
							id="pinNote"
						/>
						<label class="custom-control-label" for="pinNote"
							>Pinned</label
						>
					</div>
					<div class="ml-auto">
						<button
							@click="submit"
							class="btn btn-secondary shadow-0 ml-auto waves-effect waves-themed"
						>
							<span
								v-show="loading"
								class="spinner-border spinner-border-sm"
								role="status"
								aria-hidden="true"
							></span>
							Add Note
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'AddNote',
	data() {
		return {
			loading: false,
			text: null,
			is_pinned: true,
		};
	},
	methods: {
		submit() {
			this.loading = true;
			axios
				.post('/orders/notes/save', {
					order_id,
					text: this.text,
					is_pinned: this.is_pinned,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.dispatch(
							'order/updateNotes',
							resp.data.records
						);
						this.is_pinned = true;
						this.text = null;
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => (this.loading = false));
		},
	},
};
</script>
