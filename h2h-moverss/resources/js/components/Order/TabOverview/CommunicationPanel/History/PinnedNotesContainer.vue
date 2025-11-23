<template>
	<div
		class="position-absolute d-flex align-content-center pinned-notes-container"
	>
		<transition name="fade" mode="out-in">
			<div class="card border border-primary flex-fill">
				<div
					class="card-header py-2 pr-2 d-flex align-items-center flex-wrap bg-primary-300"
				>
					<div class="fs-xs mr-auto pr-3">
						<i class="fas fa-map-pin mr-2"></i>
						<b
							><span v-if="pinnedNotes.length > 1"
								>{{ showIndex }} of
								{{ pinnedNotes.length }} pinned notes.
							</span>
							Note
						</b>
						by {{ author }}
					</div>
					<div class="d-flex position-relative pr-2 fs-xs">
						{{ currentNote.timestamp | localDateTime }}
					</div>
				</div>
				<div
					class="card-body fs-sm py-2"
					v-html="currentNote.item.text"
				></div>
			</div>
		</transition>
		<div class="my-0" v-if="pinnedNotes.length > 1">
			<a href="next" @click.prevent="gotoNext">
				<i class="fal fa-5x fa-angle-right"></i>
			</a>
		</div>
	</div>
</template>

<script>
import localDateTime from '@/filters/localDateTime.filter';
import { mapGetters } from 'vuex';

export default {
	name: 'PinnedNotesContainer',
	data: () => ({
		pinnedNoteIndex: 0,
	}),
	computed: {
		author() {
			if (this.currentNote.item?.author?.name)
				return this.currentNote.item?.author?.name;
			return '';
		},
		...mapGetters({
			pinnedNotes: 'order/getOrderPinnedNotes',
			pinnedNotesV2: 'order/getOrderPinnedNotesV2',
		}),
		currentNote() {
			return this.pinnedNotes[this.pinnedNoteIndex];
		},
		showIndex() {
			return this.pinnedNoteIndex + 1;
		},
	},
	methods: {
		gotoNext() {
			if (this.pinnedNoteIndex + 1 < this.pinnedNotes.length)
				this.pinnedNoteIndex++;
			else this.pinnedNoteIndex = 0;
		},
	},
	filters: {
		localDateTime,
	},
};
</script>

<style scoped>
.pinned-notes-container {
	top: -80px;
	z-index: 10;
	width: calc(100% - 35px);
}
</style>
