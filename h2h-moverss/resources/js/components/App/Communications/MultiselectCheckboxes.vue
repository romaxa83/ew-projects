<template>
	<div>
		<multiselect
			@select="onSelect($event)"
			@remove="onRemove($event)"
			v-model="selected"
			:options.sync="checkOptions"
			v-bind="multiselectProps"
		>
			<template v-slot:selection="{ values, search, isOpen }">
				<span
					class="d-inline-block fs-nano ml-2"
					v-if="
						!multiselectProps.searchable &&
						(isOpen || values.length)
					"
				>
					{{ placeholder }}: {{ values.length }} selected
				</span>
			</template>
			<template v-slot:placeholder>
				<div class="fs-nano ml-2">{{ placeholder }}: any</div>
			</template>
			<template v-slot:option="option">
				<div class="d-flex option-row fs-nano">
					<div
						class="mr-1 checkbox-container"
						style="margin-top: 0.1rem"
					>
						<input
							type="checkbox"
							class="checkbox-default"
							v-model="option.option.checked"
							@focus.prevent
						/>
						<span class="checkbox-virtual"></span>
					</div>
					<div>
						{{ option.option.label }}
					</div>
				</div>
			</template>
		</multiselect>
	</div>
</template>

<script>
import 'vue-multiselect/dist/vue-multiselect.min.css'; // import "vue-multiselect-bootstrap-theme/dist/vue-multiselect-bootstrap4.scss"
import './../../../../sass/vue-multiselect-bootstrap4.scss';

import Multiselect from 'vue-multiselect';

export default {
	name: 'MultiselectCheckboxes',
	props: ['value', 'options', 'multiselectProps'],
	data() {
		return {
			// selected: [],
			checkOptions: [],
		};
	},
	mounted() {
		this.checkOptions = this.options.slice().map((v) => {
			return {
				...v,
				...{ checked: this.value && this.value.includes(v.value) },
			};
		});
	},
	computed: {
		selected: {
			get() {
				this.checkOptions = this.checkOptions.map((v) => {
					return {
						...v,
						...{
							checked:
								this.value &&
								Array.isArray(this.value) &&
								this.value.findIndex(
									(item) => item.value == v.value
								) >= 0,
						},
					};
				});
				return this.value;
			},
			set(newVal) {
				this.$emit('input', newVal);
				// this.$emit('input', val);
			},
		},
		placeholder() {
			return this.multiselectProps.placeholder
				? this.multiselectProps.placeholder
				: '';
		},
		bindedProps() {
			return this.multiselectProps;
		},
	},
	methods: {
		onSelect(option) {
			let index = this.checkOptions.findIndex(
				(item) => item.value == option.value
			);
			// this.$set(this.checkOptions, 'index', {...})
			// const O = {...this.checkOptions[index]};
			// O.checked = true;
			// this.checkOptions.splice(index, 1, O)
			this.checkOptions[index].checked = true;
			//if (!this.multiple)
		},
		onRemove(option) {
			// console.log(option)
			let index = this.checkOptions.findIndex(
				(item) => item.value == option.value
			);
			// const O = {...this.checkOptions[index]};
			// O.checked = false;
			// this.checkOptions.splice(index, 1, O)
			this.checkOptions[index].checked = false;
		},
	},
	components: {
		Multiselect,
	},
	watch: {
		// selected(newVal, oldVal) {
		//     this.$emit('input', Array.isArray(newVal) ? newVal.map(v => v.value) : newVal);
		// },
		// value(newVal, oldVal) {
		//     console.log('newVal', newVal)
		//     if (newVal.length) {
		//         this.selected = this.checkOptions.slice().filter(item => newVal.includes(item.value));
		//         for (const v of newVal) {
		//             let index = this.checkOptions.findIndex(item => item.value == v);
		//             this.checkOptions[index].checked = true;
		//         }
		//     } else
		//         this.selected = newVal;
		// },
	},
};
</script>

<style>
.multiselect__placeholder {
	margin-left: 0 !important;
}

.multiselect__option--highlight {
	background: #f8f9fa;
	color: initial;
}

.multiselect__option--selected {
	background: #fff;
	color: initial;
	font-weight: initial;
}

.multiselect__option--selected.multiselect__option--highlight {
	background: #fff;
	color: initial;
}

.checkbox-container {
	display: block;
	position: relative;
	padding-left: 20px;
	margin-bottom: 12px;
	cursor: pointer;
	font-size: 22px;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

/* скрываем дефолтный флажок */
.checkbox-container .checkbox-default {
	position: absolute;
	opacity: 0;
}

/* Создаем кастомный checkbox */
.checkbox-virtual {
	position: absolute;
	top: 0;
	left: 0;
	height: 15px;
	width: 15px;
	border: 1px solid #ccc;
	background-color: #fff;
}

/* При наведении указателя мыши перекрашиваем */
.checkbox-container:hover .checkbox-default ~ .checkbox-virtual {
	background-color: #fff;
}

/* Когда отмечен, то красим в зеленый цвет */
.checkbox-container .checkbox-default:checked ~ .checkbox-virtual {
	background-color: #fff;
}

/* Создаем когда отмечено (не видно, когда не отмечено) */
.checkbox-virtual:after {
	content: '';
	position: absolute;
	display: none;
}

/* Показываем когда отмечен */
.checkbox-container .checkbox-default:checked ~ .checkbox-virtual:after {
	display: block;
}

/* Стили индикатора */
.checkbox-container .checkbox-virtual:after {
	left: 3px;
	top: 0px;
	width: 8px;
	height: 10px;
	border: solid #666;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}

/*.checkbox-label  #e8e8e8 {*/
/*    display: block;*/
/*}*/

/*.test {*/
/*    position: absolute;*/
/*    right: 1vw;*/
/*}*/
</style>
