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
import ConnectorList from './components/ConnectorList';
// @ts-ignore
import ServerList from "./components/ServerList.vue";
// @ts-ignore
import NotificationHolder from './components/notifications/NotificationHolder.vue';
import store from './store/LoginStore';
import {mapGetters} from "vuex";
import axios from 'axios';
import config from "./config";
import router from './routes/router';

new Vue({
    el: '#app',
    router,
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
        ServerList,
        ConnectorList,
        NotificationHolder
    },
    template: `
        <div>
            <notification-holder></notification-holder>
            <nav class="navbar sticky-top navbar-expand-md navbar-dark bg-dark">
                <a class="navbar-brand" href="#">MC-SMP</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse w-100 justify-content-between" id="navigation">
                    <div class="navbar-nav">
                        <router-link to="/" class="nav-item nav-link">Home</router-link>
                        <router-link to="/gallery" class="nav-item nav-link">Gallery</router-link>
                        <router-link to="/servers" class="nav-item nav-link">Servers</router-link>
                        <router-link to="/about" class="nav-item nav-link">About</router-link>
                    </div>
                    <div class="dropdown">
                        <a v-if="readableName !== undefined" class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                            Logged in as: {{ readableName }}
                        </a>
                        <div class="dropdown-menu w-100">
                            <a href="#" class="dropdown-item" v-if="isLoggedIn" @click.prevent="logout">
                                Logout
                            </a>
                        </div>
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
            <div class="container-fluid mt-3" v-if="isLoggedIn">
                <keep-alive>
                    <router-view class="row"></router-view>
                </keep-alive>
            </div>
        </div>`,
    async created() {


        axios.interceptors.response.use(response => response, error => {
            if (error.response && error.response.data.message === "Error with token") {
                this.$store.commit("invalidateLogin");
                config.eventBus.$emit('notify-error', "There was an error with your current session, please login.");
            }
            return Promise.reject(error);
        })
        const key = localStorage.getItem("api_key");
        if (key) {
            this.$store.commit('assignLogin', { key })
            await this.$store.dispatch("getUserInfo");
            await this.$store.dispatch("connectors/getConnectors");
        }
    }
});

