import { AxiosHelper } from '@/helpers/axiosHelper';

export function getSession(id) {
	return AxiosHelper({
		url: '/orders/info',
		data: {
			id,
		},
	});
}

export function getPeaksDates() {
	return AxiosHelper({
		url: '/orders/works/peaks-days',
	});
}

export function getUsers() {
	return AxiosHelper({
		url: '/settings/users-list',
	});
}

export function apiWaypoints(mode, data, method = 'post') {
	return AxiosHelper({
		url: '/orders/waypoints/' + mode,
		method,
		data,
	});
}

export function apiOrderClient(mode, data) {
	return AxiosHelper({
		url: '/client/' + mode,
		data,
	});
}

export function zadarmaRequest(method, data, options = {}) {
	return AxiosHelper({
		...options,
		url: '/pbx/' + method,
		data,
	});
}
