import VueRouter, {RouteConfig, Route} from 'vue-router';
import Vue from 'vue';
// @ts-ignore
import Dashboard from "../components/Dashboard.vue";
// @ts-ignore
import ServerEditorList from "../components/servers/ServerEditorList.vue";
// @ts-ignore
import UserSettings from '../components/users/UserSettings.vue';
// @ts-ignore
import UserSettingsGeneral from '../components/users/UserSettingsGeneral.vue';
import store from '../store/LoginStore';

Vue.use(VueRouter);

export default new VueRouter({
    mode: 'history',
    routes: <Array<RouteConfig>> [
        {
            path: '/',
            component: Dashboard
        },
        {
            path: '/servers',
            component: ServerEditorList
        },
        {
            path: '/account',
            component: UserSettings,
            children: [
                {
                    path: 'general',
                    component: UserSettingsGeneral
                }
            ],
            beforeEnter: (to: Route, from: Route, next) => {
                if (!store.getters.isLoggedIn) {
                    next("/");
                } else {
                    next();
                }
            }
            
        }
    ],
});