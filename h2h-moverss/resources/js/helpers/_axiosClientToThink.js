/**
 * Axios Wrapper with default success/error actions
 * v.2.0
 */
import axios from "axios"

let token = document.head.querySelector('meta[name="csrf-token"]');
if (!token) {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
if (
    process.env.NODE_ENV !== "production"
    && typeof window.phpdebugbar !== "undefined"
) {
    axios.interceptors.response.use((response) => {
        phpdebugbar.ajaxHandler.handle(response.request);
        return response;
    });
}

axios.interceptors.response.use((response) => {
        return response;
    }, (error) => {
        if (error?.response?.status && error?.response?.status == 401) {
            window.location.href = '/login';
        }
        return error;
    }
);

const axiosInstance = axios.create({
    headers: {
        'X-CSRF-TOKEN': token.content
    }
});


const request = (option) => {
    const {url, method, params, data, headersType, responseType} = option
    return axiosInstance({
        url,
        method,
        params,
        data,
        responseType,
    })
}

function catchAxiosError(AxiosError) {
    console.log(AxiosError)
    if (AxiosError.response.status == 401) {
        window.location.replace("/login");
    }
}

function get(url, config) {
    let args = {method: 'get', url};
    args = config ? {...args, ...config} : args;

    return request(args)
        .catch(async (AxiosError) => catchAxiosError(AxiosError));
}

function post(url, data, config) {
    let args = {method: 'post', url, data};
    args = config ? {...args, ...config} : args;
    return request(args)
        .catch(async (AxiosError) => catchAxiosError(AxiosError));
}

// export block
export const _axiosClientToThink = {
    get: get,
    post: post,
};
