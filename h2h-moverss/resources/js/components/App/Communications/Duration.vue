<template>
	<div class="duration">{{ formattedTime }}</div>
</template>

<script>
export default {
	props: {
		startTimestampInSeconds: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			time: Math.floor(Date.now() / 1000 - this.startTimestampInSeconds),
			intervalId: null,
		};
	},
	computed: {
		formattedTime() {
			const hours = String(Math.floor(this.time / 3600)).padStart(2, '0');
			const minutes = String(
				Math.floor((this.time % 3600) / 60)
			).padStart(2, '0');
			const seconds = String(this.time % 60).padStart(2, '0');
			return `${hours}:${minutes}:${seconds}`;
		},
	},
	methods: {
		startTimer() {
			this.intervalId = setInterval(() => {
				this.time++;
			}, 1000);
		},
		stopTimer() {
			clearInterval(this.intervalId);
		},
	},
	mounted() {
		this.startTimer();
	},
	beforeUnmount() {
		this.stopTimer();
	},
};
</script>
