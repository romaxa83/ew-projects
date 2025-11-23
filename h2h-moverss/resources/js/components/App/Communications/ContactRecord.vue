<template>
	<li
		:class="{
			'bg-primary-100 selected': isSelected,
			'has-no-answer': hasNoAnswer,
			'starred-record': favorite,
		}"
		@mouseover="isHovered = true"
		@mouseleave="isHovered = false"
	>
		<a
			href="/contact"
			@click.prevent="$emit('select', event.uid)"
			class="d-flex align-items-center py-2 pl-4 pr-2"
		>
			<span class="mr-2">
				<!--                                                               <button class="ml-0 btn btn-default btn-lg btn-icon rounded-circle">AY</button>-->
				<span
					class="profile-image profile-image-md rounded-circle d-inline-block"
					:class="{ 'status status-danger': hasNoAnswer }"
					style="
						background-image: url('/smartadmin/img/demo/avatars/avatar-m.png');
						background-size: cover;
					"
				></span>
			</span>
			<span
				class="d-flex flex-column flex-1 ml-1 overflow-hidden"
				:title="recordTitle"
			>
				<span class="name">{{ recordTitle }}</span>
				<span class="msg-a fs-sm" v-html="recordText"></span>
				<span
					class="fs-nano text-muted mt-1"
					v-if="showFindedByText"
					v-html="findedByText"
				></span>
				<span class="fs-nano text-muted mt-1">{{ datetime }}</span>
			</span>
			<h3 v-if="managerAbbr">
				<span class="badge badge-primary">{{ managerAbbr }}</span>
			</h3>
		</a>
		<div
			v-if="isHovered"
			class="position-absolute d-flex"
			style="bottom: 5px; right: 3px"
		>
			<div
				class="ml-2 cursor-pointer"
				:class="{ 'starred-icon': favorite }"
				@click="toggleStarred"
			>
				<i v-show="favorite" class="fas fa-star"></i>
				<i v-show="!favorite" class="fal fa-star"></i>
			</div>
		</div>
	</li>
</template>

<script>
import { axiosPromise } from '@/helpers/axiosPromise';
import { COMMUNICATION_STATUS_NO_ANSWER } from '@/store/modules/constants';
import { BDropdown, BDropdownItem } from 'bootstrap-vue';

export default {
	name: 'ContactRecord',
	props: ['event', 'index', 'datetime'],
	data() {
		return {
			showMarkBtn: false,
			favoriteOld: false,
			isHovered: false,
		};
	},
	methods: {
		toggleStarred() {
			// this.favorite = !this.favorite;
			this.$store.commit(
				'communicationsFlow/setContactsRecordsSpinner',
				true
			);
			return axiosPromise(
				axios.post('/communications/markStarred', {
					conversation: this.event,
					starred: !this.favorite,
				})
			)
				.then((data) => {
					this.$store.commit(
						'communicationsFlow/changeRecordStarredState',
						{
							uid: this.event.uid,
							starred: !this.favorite,
						}
					);
					this.$store.commit(
						'communicationsFlow/setContactsRecordsSpinner',
						false
					);
				})
				.catch((error) => {
					App.Forms.simpleErrors(error);
					this.$store.commit(
						'communicationsFlow/setContactsRecordsSpinner',
						false
					);
				});
		},
	},
	components: {
		BDropdownItem,
		BDropdown,
	},
	computed: {
		favorite() {
			return this.event.starred;
		},
		showFindedByText() {
			if (this.event.findedByText) return true;
			return false;
		},
		findedByText() {
			if (this.event.findedByText) return this.event.findedByText;
			return '';
		},
		managerAbbr() {
			if (this.event.managerAbbr) {
				let acronym = this.event.managerAbbr.slice(0, 2);
				let splitted = this.event.managerAbbr.split(/\s/);
				if (splitted.length > 1) {
					acronym = splitted.reduce(
						(response, word) => (response += word.slice(0, 1)),
						''
					);
				}
				return acronym;
			}
			return null;
		},
		hasNoAnswer() {
			return !this.event.isAnswered;
		},
		selectedContact() {
			return this.$store.state.communicationsFlow.selectedContact;
		},
		isSelected() {
			if (
				this.selectedContact &&
				this.selectedContact.uid === this.event.uid
			)
				return true;
			return false;
		},
		item() {
			return this.event.item;
		},
		recordText() {
			let text = '';
			//if()
			if (
				this.event.type == 'CallsEvents' &&
				this.item.event == 'NOTIFY_OUT_END'
			) {
				text += '<i class="fas fa-long-arrow-left"></i> Outbound call';
			} else if (
				this.event.type == 'CallsEvents' &&
				this.item.event == 'NOTIFY_END'
			) {
				text +=
					'<i class="fas fa-long-arrow-right"></i> Inbound call ' +
					this.item.caller_id;
			}
			if (this.event.type == 'CallsEvents') {
				let badgeClass = 'danger';
				if (this.item.disposition == 'answered') badgeClass = 'success';

				text +=
					`<div><span class="badge badge-${badgeClass}">` +
					this.item.disposition +
					'</span></div>';
			}
			if (this.event.type == 'EventAfterCall') {
				if (this.item.type == 'out') {
					text +=
						'<i class="fas fa-long-arrow-left"></i> Outbound call';
				} else if (this.item.type == 'in') {
					text +=
						'<i class="fas fa-long-arrow-right"></i> Inbound call ' +
						this.item.caller_number;
				}
				let badgeClass = 'success';
				if (this.item.status === COMMUNICATION_STATUS_NO_ANSWER) {
                    badgeClass = 'danger';
                }
                if (!!this.item.status) {
                    text +=
                        `<div><span class="badge badge-${badgeClass}">` +
                        this.item.status +
                        '</span></div>';
                }
			}

			if (this.event.type == 'SmsEvents') {
				if (this.item.inbound === 1)
					text +=
						'<i class="fas fa-long-arrow-right"></i> Inbound Zadarma SMS';
				else
					text +=
						'<i class="fas fa-long-arrow-left"></i> Outbound Zadarma SMS';
			}
			if (this.event.type == 'TwilioSms') {
				if (this.item.direction == 'outbound-api')
					text +=
						'<i class="fas fa-long-arrow-left"></i> Outbound Twilio SMS';
				if (this.item.direction == 'inbound')
					text +=
						'<i class="fas fa-long-arrow-right"></i> Inbound Twilio SMS';
			}
			if (this.event.type == 'Message') {
				if (this.item.tag == 'sent')
					text += '<i class="fas fa-long-arrow-left"></i> ';
				if (this.item.tag == 'inbox')
					text += '<i class="fas fa-long-arrow-right"></i> ';
				text +=
					this.item.tag.charAt(0).toUpperCase() +
					this.item.tag.slice(1) +
					' email';
			}
			if (
				this.event.type == 'Activity' &&
				this.event.item.type == 'customer.inventory.save'
			) {
				text += '<i class="fas fa-edit"></i> Inventory edited';
			}

			return text;
		},
		recordTitle() {
			if (this.event.client) {
				return this.event.client.name + ' ' + this.event.client.lname;
			}
			let title = this.event.channelContact;
			// if (this.event.type == 'CallsEvents' )
			//     title = '+' + title;
			return title;
		},
	},
};
</script>

<style scoped>
.has-no-answer {
	background-color: #feb7d930;
}

.starred-icon {
	color: #ff9f42;
}

.starred-record {
	background-color: #ff9f4259;
}
</style>
