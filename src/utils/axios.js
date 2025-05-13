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

export default api;
