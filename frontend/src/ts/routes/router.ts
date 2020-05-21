import VueRouter, {RouteConfig} from 'vue-router';
import Vue from 'vue';

Vue.use(VueRouter);

export default new VueRouter({
    mode: 'history',
    routes: <Array<RouteConfig>> [
       {
           path: ''
       },
    ]
});