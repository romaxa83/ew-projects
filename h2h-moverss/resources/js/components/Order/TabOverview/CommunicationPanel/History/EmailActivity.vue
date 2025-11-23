<template>
	<li class="d-flex outbound-block">
		<!--        <div>-->
		<!--            <button class="btn btn-lg btn-white btn-icon activity-timeline-icon-block rounded-circle js-waves-off">-->
		<!--                <i title="messages"-->
		<!--                   class="fal fa-2x fa-envelope"></i>-->
		<!--            </button>-->
		<!--        </div>-->
		<div class="card card-zoom-hover">
			<div
				class="card-header py-2 pr-2 d-flex align-items-center flex-wrap"
			>
				<div class="mr-1"><i class="fas fa-envelope"></i></div>
				<div class="fs-xs mr-auto pr-3 text-muted">
					<b>Email template sended via "{{ item.miscs.provider }}"</b>
				</div>
				<div class="d-flex position-relative pr-2 fs-xs text-muted">
					{{ datetime }}
				</div>
			</div>
			<div class="card-body fs-sm py-2">
				<div class="fs-md">
					<i class="fas fa-long-arrow-left pr-1"></i> Outbound email
					to: <{{ item.miscs.to }}>
				</div>
				<div class="text-info">{{ item.miscs.text }}</div>
				<!--                <a href="preview" v-b-modal="modalId" @click.prevent><u>{{ item.subject }}</u></a>-->
				<!--                <del>Description about task. Tra la-la-la-la</del>-->
			</div>
		</div>
	</li>
</template>

<script>
export default {
	name: 'EmailActivity',
	props: ['record', 'datetime'],
	computed: {
		item() {
			return this.record.item;
		},
		author() {
			if (this.item.author.employee)
				return (
					this.item.author.employee.name +
					' ' +
					this.item.author.employee.l_name
				);
			return this.item.author.name;
		},
		// subject() {
		//     if (this.item.subject.length < 2)
		//         return '(Empty subject)';
		//     return this.item.subject;
		// },
		// directionIcon() {
		//     if (this.item.tag == 'inbox') {
		//         return '<i class="fas fa-long-arrow-right pr-1"></i> ';
		//     }
		//     return '';
		// },
		// directionType() {
		//     if (this.item.tag == 'inbox') {
		//         return 'Incoming email from: ' + this.item.miscs?.from?.name + ' <' + this.item.miscs?.from?.email + '>';
		//     } else {
		//         return '[' + this.item.tag + ']';
		//     }
		// }
		// author() {
		//     if (this.item.author.employee)
		//         return this.item.author.employee.name + ' ' + this.item.author.employee.l_name;
		//     return this.item.author.name;
		// }
	},
};
</script>
