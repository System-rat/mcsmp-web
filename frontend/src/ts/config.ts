import axios from 'axios';
import Vue from 'vue';

axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
export default {
    apiUrl: "/api",
    baseUrl: "http://localhost:8080",
    eventBus: new Vue()
}