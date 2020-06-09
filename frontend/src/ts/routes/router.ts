import VueRouter, {RouteConfig} from 'vue-router';
import Vue from 'vue';
// @ts-ignore
import Dashboard from "../components/Dashboard.vue";
// @ts-ignore
import ServerEditorList from "../components/servers/ServerEditorList.vue";

Vue.use(VueRouter);

export default new VueRouter({
    mode: 'history',
    routes: <Array<RouteConfig>> [
        {
            path: '/',
            component: Dashboard
        },
        {
            path: '/gallery',
            component: Dashboard
        },
        {
            path: '/servers',
            component: ServerEditorList
        }
    ],
});