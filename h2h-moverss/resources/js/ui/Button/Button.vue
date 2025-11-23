<template>
	<button
		class="btn waves-effect waves-themed"
		:class="computedClassNames"
		:type="type"
        v-on="$listeners"
	>
		<slot />
	</button>
</template>

<script>
import clsx from 'clsx';
import { uiButtonThemeClasses } from './utils/class-names';

const classNames = Object.keys(uiButtonThemeClasses);

export default {
	name: 'UiButton',
	props: {
		type: {
			type: String,
			default: 'button',
		},
		wavesEffect: {
			type: Boolean,
			default: true,
		},
		theme: {
			type: String,
			default: 'primary',
			validate: (value) => classNames.includes(value),
		},
	},
	computed: {
		computedClassNames() {
			return clsx(
				uiButtonThemeClasses[this.theme],
				this.wavesEffect && 'waves-effect waves-themed'
			);
		},
	},
};
</script>
