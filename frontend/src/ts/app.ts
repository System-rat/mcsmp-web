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
import '../../../node_modules/popper.js';
import '../../../node_modules/bootstrap/js/src/index';
import '../css/app.scss';
import Vue from 'vue';
// @ts-ignore
import Login from './Login';
// @ts-ignore
import ServerList from "./components/ServerList.vue";
import store from './store/LoginStore';
import {mapGetters} from "vuex";
import axios from 'axios';
import config from "./config";

new Vue({
    el: '#app',
    store,
    methods: {
        async doStuff() {
            const response = await axios.get(config.baseUrl + "/api/server/available_servers");
            alert(response.data);
        },
        async logout() {
            await this.$store.dispatch("logout");
        }
    },
    computed: {
        ...mapGetters([
            "isLoggedIn",
            "readableName"
        ])
    },
    components: {
        Login,
        ServerList
    },
    template: `
        <div>
            <nav class="navbar sticky-top navbar-expand-md navbar-dark bg-dark">
                <a class="navbar-brand" href="#">MC-SMP</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse" id="navigation">
                    <div class="navbar-nav">
                        <a class="nav-item nav-link active" href="/">Home</a>
                        <a class="nav-item nav-link" href="/">Gallery</a>
                        <a class="nav-item nav-link" href="/">Servers</a>
                        <a class="nav-item nav-link" href="/">About</a>
                    </div>
                </div>
                <div class="dropdown">
                    <a v-if="readableName !== undefined" class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        Logged in as: {{ readableName }}
                    </a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" v-if="isLoggedIn" @click.prevent="logout">
                            Logout
                        </a>
                    </div>
                </div>
            </nav>
            <div class="jumbotron" v-if="!isLoggedIn">
                <h1 class="display-4"> MineCraft - Server Management Platform</h1>
                <p class="lead"> Manage MineCraft servers and more!</p>
                <hr class="my-4">
                <transition name="fade">
                    <login></login>
                </transition>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <server-list v-if="isLoggedIn"></server-list>
                </div>
            </div>
        </div>`,
    async created() {


        axios.interceptors.response.use(response => response, error => {
            if (error.response.data.message === "Error with token") {
                this.$store.commit("invalidateLogin");
                alert("There was an error with your current session, please login.");
            }
            return Promise.reject(error);
        })
        const key = localStorage.getItem("api_key");
        if (key) {
            this.$store.commit('assignLogin', { key })
            await this.$store.dispatch("getUserInfo");
        }
    }
});

