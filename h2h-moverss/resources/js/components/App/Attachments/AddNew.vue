<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>Add new file</h2>
		</div>
		<div class="panel-container">
			<div class="panel-content">
				<input
					id="attachFiles"
					type="file"
					multiple
					ref="files"
					v-on:change="handleFileUpload()"
					class="form-control-file"
				/><br />

				<textarea
					v-model="description"
					class="form-control"
					rows="3"
					:maxlength="maxLength"
					placeholder="Description text"
				></textarea>

				<div class="d-flex justify-between mt-2">
					<div class="mr-1">
						<span class="mr-4"> <b>Description max length:</b> {{ maxLength }} characters</span>
                        <br>
						<span class="mr-4"> <b>Max file size:</b> 16 Mb</span>
					</div>
                    <div class="shrink-0">
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
                            Upload
                        </button>
                    </div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosError } from 'axios';

export default {
	name: 'AddNew',
	data() {
		return {
			loading: false,
			description: null,
			files: {},
            maxLength: 255,
		};
	},
	props: {
		id: {
			type: Number,
			required: true,
		},
		type: {
			type: String,
			required: true,
		},
	},
	methods: {
		handleFileUpload() {
			this.files = this.$refs.files.files;
		},
		submit() {
			this.loading = true;

			let formData = new FormData();

			for (let i = 0; i < this.files.length; i++) {
				let file = this.files[i];
				formData.append('files[' + i + ']', file);
			}

			formData.append('type', this.type);
			formData.append('id', this.id);
			if (this.description)
				formData.append('description', this.description);

			axios
				.post('/attachments/create', formData, {
					headers: {
						'Content-Type': 'multipart/form-data',
					},
				})
				.then((resp) => {
					if (resp instanceof AxiosError) {
						App.Forms.simpleErrors(resp);
					} else {
						if (resp.data?.success === true) {
							this.description = null;
							this.files = {};
							$('#attachFiles').val('');

							this.$emit('loadData');
						} else {
							App.Forms.simpleErrors(resp.data);
						}
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
