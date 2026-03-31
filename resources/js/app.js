import axios from 'axios';
import "../../public/themes/default/fonts/urbanist/urbanist.css";
import "../../public/themes/default/fonts/iconly/iconly.css";
import "../../public/themes/default/fonts/public/public.css";
import "../../public/themes/default/fonts/fontawesome/fontawesome.css";

/* Start axios code*/
const API_URL = process.env.MIX_HOST || '';
const API_KEY = process.env.MIX_API_KEY || '';

axios.defaults.baseURL = API_URL + '/api';

axios.interceptors.request.use(
    config => {
        config.headers['x-api-key'] = API_KEY;
        const vuexData = localStorage.getItem('vuex');
        if (vuexData) {
            try {
                const vuex = JSON.parse(vuexData);
                const token = vuex.auth ? vuex.auth.authToken : null;
                if (token) {
                    config.headers['Authorization'] = `Bearer ${token}`;
                }

                if (vuex.globalState && vuex.globalState.lists) {
                    config.headers['x-localization'] = vuex.globalState.lists.language_code;
                }
            } catch (e) {
                console.error('Failed to parse vuex from localStorage', e);
            }
        }
        return config;
    },
    error => Promise.reject(error),
);
/* End axios code */

console.log('Admin Panel JS initialized');
