import { AxiosHelper } from '@/helpers/axiosHelper';
import { download } from '@/helpers/download';

/**
 * @param {{
 *   buttons: import('jquery').jQuery;
 *   getFilterPayload(): Object;
 * }} options
 */
export function exportButtons({ buttons, getFilterPayload }) {
    buttons.on('click', function () {
		const button = $(this);
		const endpoint = button.data('endpoint');
		if (!endpoint) return;
		button.prop('disabled', true);
		button.addClass('_loading');

		AxiosHelper({
			method: 'post',
			url: endpoint,
			data: getFilterPayload(),
		})
			.then(({ link }) => {
				download(link);
			})
			.finally(() => {
				button.prop('disabled', false);
				button.removeClass('_loading');
			});
	});
}
