<template>
	<div class="panel">
		<div
			class="panel-hdr border-faded border-top-0 border-right-0 border-left-0 shadow-0"
		>
			<div v-if="$slots.title" class="flex-1" :class="titleSlotClassName">
				<slot name="title"></slot>
			</div>
			<h2 v-else-if="title">{{ title }}</h2>
			<div
				v-if="$slots.toolbar"
				class="panel-toolbar"
				:class="toolbarSlotClassName"
			>
				<slot name="toolbar"></slot>
			</div>
		</div>
		<div class="panel-container show">
			<div class="panel-content">
				<slot></slot>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'UiPanel',
	props: {
		title: {
			type: String,
			required: false,
		},
		titleSlotClassName: {
			type: String,
			required: false,
		},
		toolbarSlotClassName: {
			type: String,
			required: false,
		},
	},
};
</script>
