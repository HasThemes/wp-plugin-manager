import axios from 'axios';

// Create an axios instance with WordPress REST API base URL and nonce
const api = axios.create({
    baseURL: window.HTPMM?.restUrl || '/wp-json',
    headers: {
        'X-WP-Nonce': window.HTPMM?.nonce || '',
        'Content-Type': 'application/json'
    },
    withCredentials: true,
    credentials: 'same-origin'
});

export const ApiPostUrl = axios.create({
    baseURL: window.HTPMM.adminURL,
    headers: {
        'content-type': 'application/json',
        'X-WP-Nonce': window.HTPMM?.nonce
    }
});
export default api;
