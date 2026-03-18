import './bootstrap';

// Dashboard template CSS
import "@/assets/vendor/bootstrap/css/bootstrap.min.css";
import "@/assets/vendor/bootstrap-icons/bootstrap-icons.css";
import "@/assets/vendor/boxicons/css/boxicons.min.css";
import "@/assets/vendor/quill/quill.snow.css";
import "@/assets/vendor/quill/quill.bubble.css";
import "@/assets/vendor/remixicon/remixicon.css";
import "@/assets/vendor/simple-datatables/style.css";
import "@/assets/css/style.css";

// Dashboard template JS
import "@/assets/vendor/apexcharts/apexcharts.min.js";
import "@/assets/vendor/bootstrap/js/bootstrap.bundle.min.js";
import "@/assets/vendor/chart.js/chart.umd.js";
import "@/assets/vendor/echarts/echarts.min.js";
import "@/assets/vendor/quill/quill.min.js";
import "@/assets/vendor/simple-datatables/simple-datatables.js";
import "@/assets/vendor/tinymce/tinymce.min.js";
import "@/assets/vendor/php-email-form/validate.js";
import "@/assets/js/main.js";

import axios from 'axios';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import Swal from 'sweetalert2';
import * as jwt_decode from 'jwt-decode'; // Fixed import

const app = createApp(App);

// Axios default config
axios.defaults.baseURL = '/';

// Add token automatically to every request
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => Promise.reject(error));

// Response interceptor to handle expired token
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');

            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. Please log in again.',
                icon: 'warning',
                confirmButtonText: 'Ok',
            }).then(() => {
                router.push('/login');
            });
        }
        return Promise.reject(error);
    }
);

// Optional: Check token expiry on page load
function checkTokenExpiry() {
    const token = localStorage.getItem('token');
    if (!token) return false;

    try {
        const decoded = jwt_decode.default(token); // <--- Use .default here
        const now = Date.now() / 1000; // seconds
        if (decoded.exp < now) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            router.push('/login');
            return true;
        }
    } catch (e) {
        console.error('Invalid token', e);
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        router.push('/login');
        return true;
    }
    return false;
}

// Run token check immediately
checkTokenExpiry();

app.use(router).mount('#app');
