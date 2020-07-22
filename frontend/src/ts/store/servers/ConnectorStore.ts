import {ActionContext, ActionTree, GetterTree, Module, MutationTree} from "vuex";
import {LoginState} from "../LoginStore";
import axios from 'axios';
import config from "../../config";

export class Connector {
    public constructor(
        public id: number,
        public status: string,
        public host: string,
        public port: number = 1337,
        public subDirectory: string | null,
        public token: string | null,
    ) {
    }
}

export class ConnectorState {
    public connectors: Array<Connector> = [];
    public currentConnector: Connector | null = null;
}

export default <Module<ConnectorState, LoginState>> {
    namespaced: true,
    state: () => new ConnectorState(),
    mutations: <MutationTree<ConnectorState>> {
        assignConnectors(state: ConnectorState, payload: any) {
            state.connectors = payload;
            state.currentConnector = state.connectors[0];
        },
        addConnector(state: ConnectorState, payload: any) {
            state.connectors.push(payload);
        }
    },
    actions: <ActionTree<ConnectorState, LoginState>> {
        async getConnectors(context: ActionContext<ConnectorState, LoginState>) {
            const response = await axios.get(config.baseUrl + "/api/connector/get_connectors");
            if (response.status === 200) {
                const data: Array<any> = response.data.connectors;
                let connectors: Array<Connector> = [];
                data.forEach(value => {
                    connectors.push(new Connector(
                        value.id,
                        value.status,
                        value.host,
                        value.port,
                        value.subDirectory,
                        value.token
                    ))
                });
                context.commit("assignConnectors", connectors);
            }
        },

        async createConnector(context: ActionContext<ConnectorState, LoginState>, payload: any) : Promise<boolean> {
            const data = new URLSearchParams();
            data.append('host', payload.host);
            data.append('port', payload.port);
            if (payload.token !== '') {
                data.append('token', payload.token);
            }
            if (payload.sub_directory !== '') {
                data.append('sub_directory', payload.sub_directory);
            }
            const response = await axios.post(config.baseUrl + '/api/connector/create_connector', data);
            if (response.status === 200) {
                const newConnector = new Connector(
                    response.data.result,
                    response.data.status,
                    payload.host,
                    payload.port,
                    payload.sub_directory === '' ? null : payload.sub_directory,
                    payload.token === '' ? null : payload.token
                );
                await context.commit('addConnector', newConnector);
                return true;
            }
            return false;
        }
    },
    getters: <GetterTree<ConnectorState, LoginState>> {

    }
}