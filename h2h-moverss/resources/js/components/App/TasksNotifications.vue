<template>
	<div>
		<a
			href="#"
			class="header-icon"
			data-toggle="dropdown"
			title="You got 11 notifications"
		>
			<i class="fal fa-bell"></i>
			<span class="badge badge-icon">0</span>
		</a>
		<div class="dropdown-menu dropdown-menu-animated dropdown-xl">
			<div
				class="dropdown-header bg-trans-gradient justify-content-center rounded-top mt-0 mb-0 py-2"
			>
				<h4 class="text-center color-white">User Tasks</h4>
				<h4 class="d-flex m-0 w-100">
					<div class="flex-fill">
						<span class="badge badge-success d-block mb-1"
							>For today: 0</span
						>
					</div>
					<div class="flex-fill mx-1">
						<span class="badge badge-warning d-block mb-1"
							>Opened: 0</span
						>
					</div>
					<div class="flex-fill">
						<span class="badge badge-danger d-block mb-1"
							>Overdue: 0</span
						>
					</div>
				</h4>
			</div>

			<b-tabs
				nav-class="nav-tabs-clean"
				content-class="mt-3 tab-notification"
				no-fade
				justified
			>
				<b-tab title="First" title-link-class="text-center" active
					><p>I'm the first tab</p></b-tab
				>
				<b-tab title="Second" title-link-class="text-center"
					><p>I'm the second tab</p></b-tab
				>
				<!--                <b-tab title="Very, very long title"><p>I'm the tab with the very, very long title</p></b-tab>-->
				<!--                <b-tab title="Disabled" disabled><p>I'm a disabled tab!</p></b-tab>-->
			</b-tabs>

			<div
				class="py-2 px-3 bg-faded d-block rounded-bottom text-right border-faded border-bottom-0 border-right-0 border-left-0"
			>
				<a href="#" class="fs-xs fw-500 ml-auto"
					>view all notifications</a
				>
			</div>
		</div>
	</div>
</template>

<script>
import { BTabs, BTab } from 'bootstrap-vue';

export default {
	name: 'TasksNotifications',
	components: {
		BTabs,
		BTab,
	},
};
</script>
