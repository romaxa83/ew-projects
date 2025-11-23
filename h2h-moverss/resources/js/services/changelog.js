import { AxiosHelper } from '@/helpers/axiosHelper';

export const DEFAULT_SORT = 'desc';
export const DEFAULT_ALL = false;
export const ChangelogActions = {
	Created: 'created',
	Updated: 'updated',
	Deleted: 'deleted',
	Cloned: 'cloned',
};

export async function refetchChangelog({
	current,
	payload,
	onStart,
	onSuccess,
	onEnd,
}) {
	onStart();

	const requestData = {
		...current.staticRequestParams,
		sort_type: getSortParam(),
		logs_all: getLogsAllParam(),
	};

	function getSortParam() {
		return payload?.sort || current.sort || 'desc';
	}

	function getLogsAllParam() {
		if (payload?.less) {
			return false;
		}
		return !!(payload?.all || current.all);
	}

	return AxiosHelper({
		url: current.api,
		method: 'POST',
		data: requestData,
	})
		.then(({ success, logs }) => {
			if (success) {
				onSuccess({
					...getLogsApiResult(logs, requestData.sort_type),
					sort: requestData.sort_type,
					all: requestData.logs_all,
				});
			}
		})
		.finally(() => {
			onEnd();
		});
}

/**
 * @param {{ data?: Array; meta?: Object | null }} logs
 * @param {'desc' | 'asc'} [sort='desc']
 * @returns {{ data: Array, hasMore: boolean }}
 */
export function getLogsApiResult(logs, sort = 'desc') {
	const data = [];
	const reqData = logs.data || [];
	if (sort === 'desc') {
		reqData.reverse();
	}

	reqData.forEach((current) => {
		const prev = data[data.length - 1];
		if (isLooksSame(current, prev) && Array.isArray(current.details)) {
			if (!Array.isArray(prev.details)) prev.details = [];
			prev.details.push(...current.details);
		} else {
			data.push(current);
		}
	});

	if (sort === 'desc') {
		data.reverse();
	}

	return {
		data,
		hasMore: logs.meta?.last_page > logs.meta?.current_page,
	};

	function isLooksSame(current, prev) {
		return isClientCreateAction(current) && isClientCreateAction(prev);
	}

	function isClientCreateAction(item) {
		return (
			!!item &&
			item.entity === 'Client' &&
			item.action === ChangelogActions.Created
		);
	}
}
