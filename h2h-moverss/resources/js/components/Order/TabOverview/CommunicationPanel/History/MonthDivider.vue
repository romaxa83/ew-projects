<template>
	<li>
		<div class="d-flex justify-content-center">
			<div class="bg-white shadow-3 px-3 py-1 rounded-pill fs-xs fw-500">
				{{ record.text }}
			</div>
		</div>
	</li>
</template>

<script>
export default {
	name: 'MonthDivider',
	props: ['record'],
};
</script>
