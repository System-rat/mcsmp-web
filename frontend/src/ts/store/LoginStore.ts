import Vue from 'vue';
import Vuex, {ActionContext, ModuleTree, StoreOptions} from 'vuex';
import { GetterTree, MutationTree, ActionTree, Store } from 'vuex';
import axios from 'axios';
import config from '../config';

import ServerStore from "./servers/ServerStore";
import ConnectorStore from "./servers/ConnectorStore";

Vue.use(Vuex);

export class LoginState {
    public username?: string;
    public apiKey?: string = localStorage.getItem("api_key") || undefined;
    public displayName?: string;
}

let store = new Store(<StoreOptions<LoginState>>{
    state: new LoginState(),
    mutations: <MutationTree<LoginState>> {
        assignLogin: (state, payload) => {
            Vue.set(state, 'apiKey', payload.key);
            axios.defaults.headers.common["X-AUTH-TOKEN"] = payload.key;
            localStorage.setItem("api_key", payload.key);
        },
        setInfo(state: LoginState, payload: any) {
            Vue.set(state, 'username', payload.username);
            Vue.set(state, 'displayName', payload.display_name);
        },
        invalidateLogin(state: LoginState) {
            Vue.set(state, 'apiKey', undefined);
            Vue.set(state, 'username', undefined);
            Vue.set(state, 'displayName', undefined);
            localStorage.removeItem("api_key");
            axios.defaults.headers.common["X-AUTH-TOKEN"] = "";
        }
    },
    getters: <GetterTree<LoginState, LoginState>> {
        isValid: state => {
            return state.apiKey !== undefined
                && state.username !== undefined;
        },
        readableName: state => {
            return state.displayName === undefined ? state.username : state.displayName;
        },
        isLoggedIn: state => {
            return state.apiKey !== undefined;
        }
    },
    actions: <ActionTree<LoginState, LoginState>> {
        async login(context: ActionContext<LoginState, LoginState>, payload: any) {
            const login_payload = new URLSearchParams();
            login_payload.append("username", payload.username);
            login_payload.append("authenticator", payload.authenticator);
            const response = await axios.post(config.baseUrl + "/api_login", login_payload);
            if (response.status !== 200) {
                return Promise.reject(new Error(response.data));
            }
            context.commit("assignLogin", response.data);
            await context.dispatch('getUserInfo');
        },
        async getUserInfo(context: ActionContext<LoginState, LoginState>) {
            const response = await axios.get(config.baseUrl + "/api/user/get_info");
            if (response.status !== 200) {
                return Promise.reject(new Error(response.data));
            }
            context.commit('setInfo', response.data);
        },
        async logout(context: ActionContext<LoginState, LoginState>) {
            const response = await axios.get(config.baseUrl + "/api/user/logout");
            if (response.status !== 200) {
                alert("You are already logged out!");
            }
            context.commit("invalidateLogin");
        }
    },
    modules: <ModuleTree<LoginState>> {
        servers: ServerStore,
        connectors: ConnectorStore
    }
});

export default store;
