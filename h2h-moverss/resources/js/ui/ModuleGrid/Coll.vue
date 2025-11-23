<template>
	<div :class="className">
		<slot></slot>
	</div>
</template>

<script>
import clsx from 'clsx';

const validator = (v) => v >= 1 && v <= 12;

export default {
	name: 'UiColl',
	props: {
		span: { type: Number, validator },
        spanSm: { type: Number, validator },
        spanMd: { type: Number, validator },
        spanLg: { type: Number, validator },
        spanXl: { type: Number, validator },
	},
	computed: {
		className() {
			return clsx(
				this.span && `col-${this.span}`,
				this.spanSm && `col-sm-${this.spanSm}`,
				this.spanMd && `col-md-${this.spanMd}`,
				this.spanLg && `col-lg-${this.spanLg}`,
				this.spanXl && `col-xl-${this.spanXl}`
			);
		},
	},
};
</script>
