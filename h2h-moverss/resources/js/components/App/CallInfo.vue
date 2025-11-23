<template>
	<div class="call-info d-flex flex-row align-items-center flex-wrap">
		<div class="mr-4">
			Reviews count:
			{{ data.count }}
		</div>
		<div class="mr-4">
			Average score:
			{{ data.avg }}
		</div>
		<div
			class="cursor-pointer callInfo"
			data-toggle="popover"
			data-placement="bottom"
		>
			<span>Reviews</span>
			<i class="fal fa-angle-down d-inline-block ml-1 fs-md" />
		</div>
		<div class="d-none">
			<div id="popover_content" style="width: 450px">
				<div
					v-for="(el, i) of data.data"
					:class="{ 'mb-2': i < data.data.length - 1 }"
					:key="el.id"
				>
					<div class="fw-500">
						<span v-if="data.data.length > 1">{{ i + 1 }}.</span>
						Date: {{ formatDate(el.created_at) }}, Score:
						{{ el.score }}
					</div>
					<div>{{ el.details }}</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { DateService } from '@/services/date';

let popover;
export default {
	name: 'CallInfo',
	props: {
		data: {
			type: Object,
			required: true,
		},
	},
	mounted() {
		popover = $('.callInfo')
			.popover({
				content: $('#popover_content'),
				html: true,
			})
			.on('show.bs.popover', function () {
				$($(this).data('bs.popover').getTipElement()).css({
					'max-width': '500px',
					'max-height': '350px',
					'overflow-y': 'auto',
				});

				$('html').on('mouseup', function (e) {
					if (
						$(e.target).hasClass('popover') ||
						$(e.target).closest('.popover').length
					) {
						return;
					}

					$('[data-toggle="popover"]').popover('hide');
				});
			});
	},
	methods: {
		formatDate(date) {
			if (!date) return '';
			return new DateService(date).format({
				preset: 'changelog',
			});
		},
	},
};
</script>

<style lang="scss">
.call-info {
	color: var(--color-text-primary);
	font-weight: 500;
}

.popover {
	.arrow {
		display: none;
	}
}
</style>
