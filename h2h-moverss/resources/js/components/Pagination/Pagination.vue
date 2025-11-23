<template>
	<div class="row mt-3">
		<div class="col-auto mr-auto">
			<div class="mr-3 my-2 amount">{{ amount }}</div>
		</div>
		<div class="col-auto">
			<nav aria-label="Pagination">
				<ul class="pagination m-0">
					<li :class="getPageClass(prevPage)">
						<button
							class="page-link"
							aria-label="Previous"
							@click="handleChange(prevPage)"
						>
							<span aria-hidden="true">
								<i class="fal fa-chevron-left"></i>
							</span>
						</button>
					</li>
					<li
						v-for="page in pages"
						:class="getPageClass(page)"
						:key="page.url + page.label"
						:aria-current="isActive(page) ? 'page' : null"
					>
						<span v-if="isActive(page)" class="page-link">
							{{ page.label }}
						</span>
						<span v-else-if="isSpacer(page)" class="page-link">
							...
						</span>
						<button
							v-else
							class="page-link"
							@click="handleChange(page)"
						>
							{{ page.label }}
						</button>
					</li>
					<li :class="getPageClass(nextPage)">
						<button
							class="page-link"
							aria-label="Next"
							@click="handleChange(nextPage)"
						>
							<span aria-hidden="true">
								<i class="fal fa-chevron-right"></i>
							</span>
						</button>
					</li>
				</ul>
			</nav>
		</div>
	</div>
</template>

<script>
const isActive = (page) => !!page.active;
const isSpacer = (page) => !!page.spacer;

export default {
	name: 'Pagination',
	props: ['links', 'meta', 'refetching'],
	methods: {
		handleChange({ url }) {
			if (url) {
				this.$emit('pagination-change', url);
			}
		},
		getPageClass(page) {
			return {
				'page-item': true,
				active: this.isActive(page),
				spacer: this.isSpacer(page),
				disabled: this.refetching || !page.url,
			};
		},
		isActive(page) {
			return page.active;
		},
		isSpacer(page) {
			return page.spacer;
		},
	},
	computed: {
		amount() {
			const { from, to, total } = this.meta;
			if (total === 0) {
				return 'No entries';
			} else if (total === 1) {
				return 'Showing 1 entry';
			}
			return `Showing ${from} to ${to} of ${total} entries`;
		},
		pages() {
			return this.links.slice(1, -1);
		},
		prevPage() {
			return this.links[0];
		},
		nextPage() {
			return this.links[this.links.length - 1];
		},
	},
};
</script>

<style scoped>
.amount {
	color: var(--color-text-secondary);
	font-size: 14px;
	font-weight: 400;
	line-height: 17px;
}
</style>
