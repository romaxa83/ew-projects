<template>
	<table class="table">
		<thead>
			<tr>
				<th width="30%">Field</th>
				<th width="35%"><span>Old value</span></th>
				<th width="35%"><span>New value</span></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="detail in list">
				<detail-field :value="detail.field"></detail-field>
				<detail-value :value="detail.old"></detail-value>
				<detail-value :value="detail.new"></detail-value>
			</tr>
		</tbody>
	</table>
</template>

<script>
import DetailField from './DetailField';
import DetailValue from './DetailValue';

export default {
	name: 'ItemDetails',
	props: {
		list: Array,
		required: true,
	},
	components: {
		DetailField,
		DetailValue,
	},
};
</script>

<style scoped>
.table {
	margin: 0 0 20px;
	font-size: 14px;
}

thead {
	background-image: linear-gradient(to bottom, #fafafa, #f2f2f2);
}

th {
	position: relative;
}

th span::before {
	content: '';
	position: absolute;
	top: 50%;
	height: 20px;
	left: 0;
	margin-top: -10px;
	width: 1px;
	background-color: #0000000f;
}

td {
	border-width: 0;
}
</style>
