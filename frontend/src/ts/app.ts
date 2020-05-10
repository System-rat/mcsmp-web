/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
const $ = require('jquery');
// @ts-ignore
global.$ = $;
import '../../../node_modules/bootstrap/js/src/index';
import '../css/app.scss';
import Vue from 'vue';
// @ts-ignore
import Login from './Login';
import store from './store/LoginStore';
import {mapGetters} from "vuex";
import axios from 'axios';
import config from "./config";

new Vue({
    el: '#app',
    store,
    methods: {
        async doStuff() {
            const response = await axios.get(config.apiUrl);
            alert(response.data);
        }
    },
    computed: {
        ...mapGetters([
            "isLoggedIn"
        ])
    },
    components: {
        Login
    },
    template: `
        <div class="row">
            <transition name="slide">
                <login class="col-12" v-if="!isLoggedIn"></login>
            </transition>
            <button class="col-12 btn btn-primary" @click="doStuff">Do stuff</button>
        </div>`,
    created() {
        const key = localStorage.getItem("api_key");
        if (key) {
            axios.defaults.headers.common["X-AUTH-TOKEN"] = key;
        }
        axios.interceptors.response.use(response => response, error => {
            if (error.response.data.message === "Error with token") {
                this.$store.commit("invalidateLogin");
            }
            return Promise.reject(error);
        })
    }
});

