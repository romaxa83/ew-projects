<template>
	<span v-if="count" class="badge badge-danger">{{ count }}</span>
</template>

<script>
import { axiosPromise } from '@/helpers/axiosPromise';

export default {
	name: 'CommunicationUnanswered',
	data() {
		return {
			count: null,
		};
	},
	mounted() {
		this.getUnansweredRecords();
	},
	methods: {
		getUnansweredRecords() {
			return axiosPromise(
				axios.get('/communications/recordsUnanswered')
			).then((data) => {});
		},
	},
};
</script>
