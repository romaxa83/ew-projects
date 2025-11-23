/**
 * Axios Wrapper with default success/error actions
 * v.1.0
 */

/**
 * @param {Object} options
 * @param {string} [options.url]
 * @param {import('axios').Method} [options.method]
 * @param {any} [options.data]
 * @return {Promise<Awaited<any>>}
 * @constructor
 */
export async function AxiosHelper(options) {
	const client = window.axios.create({
		// baseURL: '',
		method: 'post',
	});

	try {
		const resp = await client(options);
		if (resp.data.success === true) return Promise.resolve(resp.data);
		else
			throw {
				response: {
					data: resp.data,
				},
			};
	} catch (error) {
		App.Forms.simpleErrors(
			(error.response && error.response?.data) || error
		);

		if (error?.response?.status && error?.response?.status == 401) {
			window.location.href = '/login';
		} else {
			return Promise.reject(
				(error.response && error.response.data) || error
			);
		}
	}
}
