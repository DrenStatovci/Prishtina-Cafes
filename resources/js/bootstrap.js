import axios from 'axios';
window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true; // send session cookie
axios.defaults.baseURL = '/';

export async function csrf() {
    // primes XSRF-TOKEN cookie (needed before first POST/PUT/PATCH/DELETE)
    await axios.get('/sanctum/csrf-cookie');
}

let retried = new WeakSet();
axios.interceptors.response.use(
    (r) => r,
    async (error) => {
        const cfg = error.config;
        if (error.response?.status === 419 && cfg && !retried.has(cfg)) {
            retried.add(cfg);
            await csrf();
            return axios.request(cfg);
        }
        return Promise.reject(error);
    }
);