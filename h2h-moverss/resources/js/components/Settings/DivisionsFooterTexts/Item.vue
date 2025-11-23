<template>
	<div
		class="tab-pane fade"
		:id="`divisions_${record.id}`"
		role="tabpanel"
		:class="{ show: !i, active: !i }"
		:aria-labelledby="`divisions-${record.id}-tab`"
	>
		<h3 v-text="record.title"></h3>

		<div class="panel-container show">
			<div class="panel-content">
				<ul class="nav nav-tabs" role="tablist">
					<li
						class="nav-item"
						v-for="(v, i) in record.afterwords"
						:key="v.id"
						:class="{ active: !i }"
					>
						<a
							class="nav-link"
							:class="{ active: !i }"
							data-toggle="tab"
							:href="`#divisions_${record.id}-tab-${v.id}`"
							role="tab"
						>
							{{ v.title }}
						</a>
					</li>
				</ul>
				<div class="tab-content p-3">
					<div
						class="tab-pane fade"
						v-for="(v, i) in record.afterwords"
						:key="v.id"
						:class="{ show: !i, active: !i }"
						:id="`divisions_${record.id}-tab-${v.id}`"
						role="tabpanel"
					>
						<div class="row">
							<div class="col-lg-3">
								<div class="form-group">
									<label
										class="form-label"
										:for="`d_${v.id}_name`"
										>Name</label
									>
									<input
										type="text"
										:id="`d_${v.id}_name`"
										class="form-control"
										v-model="v.name"
										readonly
									/>
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label
										class="form-label"
										:for="`d_${v.id}_title`"
										>Title</label
									>
									<input
										type="text"
										:id="`d_${v.id}_title`"
										class="form-control"
										v-model="v.title"
										@input="onTitleInput"
									/>
								</div>
							</div>
						</div>
						<ckeditor
							class="form-control rounded-0 w-100 h-100 border-0 p-0 mt-4"
							v-model="v.text"
							@focus="onEditorFocus"
							:config="getEditorConfig()"
						></ckeditor>
					</div>
				</div>
			</div>
		</div>

		<button
			@click="submit()"
			type="button"
			:class="{ 'btn-primary': is_changed, 'btn-default': !is_changed }"
			:disabled="!is_changed"
			class="text-nowrap btn waves-effect waves-themed"
		>
			<span
				v-show="updating"
				class="spinner-border spinner-border-sm"
				role="status"
				aria-hidden="true"
			></span>
			<i class="fal fa-download mr-1"></i>
			{{ updating ? 'Saving changes' : 'Save changes' }}
		</button>
	</div>
</template>

<script>
import CKEditor from 'ckeditor4-vue';
import { ckEditorConfig } from '@/helpers/ckEditorConfig';

export default {
	name: 'DivisionsFooterTextsItem',
	components: {
		ckeditor: CKEditor.component,
	},
	props: {
		i: {
			type: Number,
			required: true,
		},
		is_changed: {
			type: Boolean,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
		updating: {
			type: Boolean,
			required: true,
		},
	},
	methods: {
		getEditorConfig() {
			return {
				...ckEditorConfig(),
				height: '600px',
			};
		},
		onEditorFocus() {
			this.$emit('update:is_changed', true);
		},
		onTitleInput() {
			this.$emit('update:is_changed', true);
		},
		submit() {
			this.$emit('submit', this.i);
		},
	},
};
</script>
