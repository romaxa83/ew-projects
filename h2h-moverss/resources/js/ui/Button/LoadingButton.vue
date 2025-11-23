<template>
	<ui-button
		type="button"
		class="btn-dis-loading"
        v-on="$listeners"
		:class="loading && '_loading'"
		:theme="theme"
	>
		<span
			aria-hidden="true"
			class="spinner-border btn-dis-spinner fs-nano position-absolute"
			:class="spinnerColor"
		></span>
        <slot />
	</ui-button>
</template>

<script>
import UiButton from '@ui/Button/Button.vue';
import { darkBgThemes } from './utils/class-names';

export default {
	name: 'UiLoadingButton',
	props: {
        ...UiButton.props,
		loading: {
			type: Boolean,
			default: false,
		},
	},
	computed: {
		spinnerColor() {
			return darkBgThemes.includes(this.theme)
				? 'text-white'
				: 'text-secondary';
		},
	},
	components: {
		UiButton,
	},
    // methods: {
    //     onClick(event) {
    //         console.log('xxxx')
    //         if (!this.loading && !this.disabled) {
    //             this.$emit('click', event);
    //         }
    //     },
    // }
};
</script>
