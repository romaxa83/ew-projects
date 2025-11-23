<template>
	<calendar
		ref="calendar"
		:data-source="dataSource"
		:enable-context-menu="true"
		:enable-range-selection="true"
		:context-menu-items="contextMenuItems"
		@mouse-on-day="mouseOnDay"
		@mouse-out-day="mouseOutDay"
		@select-range="selectRange"
		@day-context-menu="contextDay"
	>
	</calendar>
</template>

<script>
const Calendar = () =>
	import(/* webpackChunkName: "Calendar" */ 'v-year-calendar');
import cloneDeep from 'lodash.clonedeep';

export default {
	name: 'YearCalendar',
	components: {
		Calendar,
	},
	props: {
		dataSource: {
			type: Array,
			default() {
				return [];
			},
		},
		entityName: {
			type: String,
			default: 'entity not set o_0',
		},
	},
	data() {
		return {
			contextMenuItems: [
				{
					text: 'Update',
					click: (evt) => this.editEvent(evt),
				},
				{
					text: 'Delete',
					click: (evt) => this.deleteEvent(this, evt),
				},
			],
		};
	},
	methods: {
		contextDay: (e) => {
			$(e.element).popover('hide');
			// $(e.element).popover('destroy');
		},
		deleteEvent: (vm, evt) => {
			let records = cloneDeep(vm.dataSource);
			records = records.filter(
				(item) => item.randomRef !== evt.randomRef
			);

			vm.$emit('update:dataSource', records);
			vm.$emit('submit');
		},
		editEvent(evt) {
			this.showModal(evt);
		},
		mouseOnDay: (e) => {
			if (e.events.length > 0) {
				let content = '';
				for (let i in e.events) {
					content +=
						'<div class="event-tooltip-content">' +
						'<div class="event-name" style="color:' +
						e.events[i].color +
						'">' +
						e.events[i].title +
						'</div>' +
						(e.events[i].description
							? '<div class="event-location">' +
							  e.events[i].description +
							  '</div>'
							: '') +
						'</div>';
				}

				$(e.element).popover({
					trigger: 'manual',
					container: 'body',
					html: true,
					content,
				});

				$(e.element).popover('show');
			}
		},
		mouseOutDay: (e) => {
			if (e.events.length > 0) {
				$(e.element).popover('dispose');
			}
		},
		saveForm(formData) {
			let records = cloneDeep(this.dataSource);
			let index = records.findIndex(
				(item) => item.randomRef === formData.randomRef
			);
			let startDate = moment(formData.startDate, 'YYYY-MM-DD');

			let record = {
				description: formData.description,
				startDate,
				endDate: startDate,
				startTime: '00:00',
				endTime: '23:59',
				type_id: parseInt(formData.type_id),
				title: $(
					'#calendar-modal select[name="type_id"] option:selected'
				).text(),
				is_virtual: false,
			};

			if (index === -1) {
				let color = '#f5bb00'; // Holiday
				if (record.type_id === 2) {
					color = '#b56ce2'; // Peak
				} else if (record.type_id === 3) {
					color = '#0ed6b9'; // Work Day
				}

				// add new
				records.push({
					id: null,
					randomRef: App.Miscs.generateToken(),
					color,
					...record,
				});
			} else {
				records[index] = {
					...records[index],
					...record,
				};
			}
			this.$emit('update:dataSource', records);
			this.$emit('submit');
		},
		selectRange(eventRange) {
			this.showModal(eventRange);
		},
		showModal(evt) {
			let self = this;
			$('#calendar-modal')
				.on('shown.bs.modal', function (e) {
					if (evt) {
						$(this)
							.find('input[name="startDate"]')
							.val(moment(evt.startDate).format('YYYY-MM-DD'));
						$(this)
							.find('input[name="randomRef"]')
							.val(evt.randomRef);

						if (evt.id) {
							$(this).find('input[name="id"]').val(evt.id);
							$(this).find('.modal-title').text('Edit busy time');
						}
						if (evt.description) {
							$(this)
								.find('input[name="description"]')
								.val(evt.description);
						}
						if (evt.type_id) {
							$(this)
								.find('select[name="type_id"]')
								.val(evt.type_id);
						}
					}

					let modal = this;
					$(this)
						.find('form')
						.submit(function (e) {
							e.preventDefault();

							let formData = $(this).serializeFormObject();
							self.saveForm(formData);
							$(modal).modal('hide');
						});

					$(this)
						.find('.dateInput')
						.each(function () {
							flatpickr(this, {
								altInput: true,
								altFormat: 'F j, Y',
								dateFormat: 'Y-m-d',
							});
						});
					e.stopPropagation();
				})
				.on('hide.bs.modal', function () {
					$(this).off('shown.bs.modal');
					$(this).find('form').off('submit');

					// reset time values to default
					$(this).off('hidden.bs.modal');
					$(this).find('input[name="description"]').val('');
					$(this).find('.modal-title').text('Add busy time');
				});
			$('#calendar-modal').modal('show');
		},
	},
};
</script>

<style scoped>
.calendar {
	min-height: 490px;
}
</style>
