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
		// clickDay(e) {
		//     this.showModal(e);
		// },
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
						e.events[i].name +
						'</div>' +
						(e.events[i].details
							? '<div class="event-location">' +
							  e.events[i].details +
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
				$(e.element).popover('hide');
				// $(e.element).popover('destroy');
			}
		},
		saveForm(formData) {
			let records = cloneDeep(this.dataSource);
			let index = records.findIndex(
				(item) => item.randomRef === formData.randomRef
			);
			let record = {
				details: formData.startTime + ' - ' + formData.endTime,
				name: formData.reason,
				startDate: new Date(
					formData.startDate.replace(/-/g, '\/') +
						' ' +
						formData.startTime
				),
				endDate: new Date(
					formData.endDate.replace(/-/g, '\/') +
						' ' +
						formData.endTime
				),
				startTime: formData.startTime,
				endTime: formData.endTime,
			};
			if (index === -1) {
				// add new
				records.push({
					id: null,
					randomRef: App.Miscs.generateToken(),
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
			let modal = $('#calendar-modal');
			modal
				.on('shown.bs.modal', function (e) {
					if (evt) {
						$(this)
							.find('input[name="startDate"]')
							.val(moment(evt.startDate).format('YYYY-MM-DD'));
						$(this)
							.find('input[name="endDate"]')
							.val(moment(evt.endDate).format('YYYY-MM-DD'));
						$(this)
							.find('input[name="randomRef"]')
							.val(evt.randomRef);

						if (evt.id) {
							$(this).find('input[name="id"]').val(evt.id);
							$(this).find('.modal-title').text('Edit busy time');
						}
						if (evt.randomRef) {
							$(this)
								.find('input[name="randomRef"]')
								.val(evt.randomRef);
						}
						if (evt.name) {
							$(this).find('input[name="reason"]').val(evt.name);
						}

						if (evt.startTime)
							$(this)
								.find('input[name="startTime"]')
								.val(evt.startTime);
						if (evt.endTime)
							$(this)
								.find('input[name="endTime"]')
								.val(evt.endTime);
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
						.find('.timeInput')
						.each(function () {
							flatpickr(this, {
								enableTime: true,
								noCalendar: true,
								altInput: true,
								altFormat: 'h:i K',
								dateFormat: 'H:i',
								minuteIncrement: 30,
								time_24hr: false,
							});
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
					$(this).find('input[name="startTime"]').val('8:00');
					$(this).find('input[name="endTime"]').val('18:00');
					$(this).find('input[name="reason"]').val('');
					$(this).find('.modal-title').text('Add busy time');
				});
			modal.modal('show');
		},
	},
};
</script>

<style scoped>
.calendar {
	min-height: 490px;
}
</style>
