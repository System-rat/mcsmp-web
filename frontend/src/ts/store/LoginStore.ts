import Vue from 'vue';
import Vuex, {ActionContext, StoreOptions} from 'vuex';
import { GetterTree, MutationTree, ActionTree, Store } from 'vuex';
import axios from 'axios';
import config from '../config';

Vue.use(Vuex);

class LoginState {
    public username?: string;
    public apiKey?: string = localStorage.getItem("api_key") || undefined;
    public displayName?: string;
}

export default new Store(<StoreOptions<LoginState>>{
    state: new LoginState(),
    mutations: <MutationTree<LoginState>> {
        assignLogin: (state, payload) => {
            state.apiKey = payload.key;
            axios.defaults.headers.common["X-AUTH-TOKEN"] = payload.key;
            localStorage.setItem("api_key", payload.key);
        },
        invalidateLogin(state: LoginState) {
            state.apiKey = undefined;
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
        }
    }
})
