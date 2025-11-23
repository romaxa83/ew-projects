/**
 * Axios Promise
 * v.1.0
 */
import {isAxiosError} from 'axios';
export async function axiosPromise(axiosInstanse) {

    return new Promise((resolve, reject) => {
        axiosInstanse
            .then((response) => {
                if(isAxiosError(response)) {
                    if(response.response?.data)
                        reject(response.response?.data)
                    else
                        reject(response.response)
                }
                else if (response.data && response.data.success && response.data.success == true) {
                    resolve(response.data);
                } else
                    reject(response.data)
            }).catch((error) => {
                // only server errors
                reject(error.response && error.response.data ? error.response.data : error);
        })
    })

    //
    // const client = window.axios.create({
    //     // baseURL: '',
    //     method: 'post',
    // });
    //
    // try {
    //     const resp = await client(options);
    //
    //     if (resp.data.success === true)
    //         return Promise.resolve(resp.data);
    //     else
    //         throw {
    //             response: {
    //                 data: resp.data
    //             }
    //         };
    // } catch (error) {
    //     App.Forms.simpleErrors(error.response && error.response.data || error);
    //
    //     return Promise.reject(error.response && error.response.data || error);
    // }
}

