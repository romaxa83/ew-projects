<template>
	<li>Undefined component: [{{ record.type }}]</li>
</template>

<script>
export default {
	name: 'Empty',
	props: ['record', 'datetime'],
};
</script>
