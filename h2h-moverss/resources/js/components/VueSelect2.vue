<template>
	<div>
		<select :class="setclass" ref="select">
			<slot></slot>
		</select>
	</div>
</template>

<script>
export default {
	name: 'VueSelect2',
	props: ['config', 'options', 'value', 'setclass', 'componentKey'],
	computed: {
		id() {
			return this._uid;
		},
	},
	methods: {
		open() {
			$(this.$refs.select).select2('open');
		},
	},
	mounted: function () {
		// console.log('select2 mounted')
		var vm = this;
		$(this.$refs.select)
			.select2(this.config)
			.on('change', function (ev, args) {
				if (!(args && 'ignore' in args)) {
					vm.$emit('input', $(this).val());
					if ($(this).val())
						vm.$emit('option', $(this).select2('data'));
				}
			})
			.on('select2:open', function () {
				//console.log(vm.config)
				// переделать пост только если есть парент
				// setTimeout(() => {
				//     const s = document.querySelector('.select2-search__field');
				//     // console.log(s.length);
				//     if (s) s.focus();
				// }, 500);
			});

		this.$nextTick(() => {
			$(this.$refs.select)
				.val(this.value)
				.trigger('change', { ignore: true });
		});
	},
	watch: {
		componentKey() {
			// console.log('select2update')
			this.$nextTick(() => {
				$(this.$refs.select)
					.val(this.value)
					.trigger('change', { ignore: true });
			});
			// $(this.$refs.select).trigger('change')
			// this.$forceUpdate();
		},
		value: function (value, oldValue) {
			$(this.$refs.select)
				.val(this.value)
				.trigger('change', { ignore: true });
		},
		// options: function (options) {
		//     // update options
		//     //     console.log(options);
		//     for (const option in options) {
		//         $(this.$refs.select)
		//             .append(new Option(option.text, option.id, false, false))
		//     }
		//     $(this.$refs.select).trigger('change', {ignore: true});
		// },
		config: function (config) {
			// update options
			$(this.$refs.select).select2(config);
		},
	},
	destroyed() {
		$(this.$refs.select).off().select2('destroy');
	},
};
</script>

<style>
/* force select2 to match bootstrap form-control-sm */
/*.select2, .select2-selection__rendered { line-height: calc(1.5em + .5rem + 2px) !important; }*/
/*.select2-container .select2-selection--single { height: calc(1.5em + .5rem + 2px) !important; }*/
/*.select2-selection__arrow { height: calc(1.5em + .5rem + 2px) !important; }*/
</style>
