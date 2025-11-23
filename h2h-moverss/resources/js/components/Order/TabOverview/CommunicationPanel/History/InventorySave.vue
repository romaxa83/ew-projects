<template>
	<li class="fs-sm text-center">{{ datetime }}. Customer edited inventory</li>
</template>

<script>
export default {
	name: 'InventorySave',
	props: ['record', 'datetime'],
	// computed: {
	//     fromValue() {
	//         return this.record.update.from.title;
	//     },
	//     toValue() {
	//         return this.record.update.to.title;
	//     },
	//     item() {
	//         return this.record.item;
	//     },
	//     attribute() {
	//         return this.item.type[0].toUpperCase() + this.item.type.slice(1);
	//         // return this.item.type;
	//     },
	//     author() {
	//         if (this.item.author?.employee)
	//             return this.item.author?.employee?.name + ' ' + this.item.author?.employee?.l_name;
	//         return this.item.author?.name;
	//     },
	// }
};
</script>
