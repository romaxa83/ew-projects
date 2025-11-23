<template>
	<fragment>
		<portal-target
			v-for="name in names"
			:key="name"
			:name="name"
		></portal-target>
	</fragment>
</template>

<script>
import { Fragment } from 'vue-fragment';
import { VuePortalNames } from '@components/VuePortalNames';

export default {
	name: 'VuePortals',
	data() {
		return {
			names: Object.values(VuePortalNames),
		};
	},
	components: {
		Fragment,
	},
};
</script>
