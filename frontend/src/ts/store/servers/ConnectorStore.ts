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
        }
    },
    getters: <GetterTree<ConnectorState, LoginState>> {

    }
}