import axios from 'axios';
import {ActionContext} from 'vuex';
import { GetterTree, MutationTree, ActionTree, Module } from 'vuex';
import { LoginState } from '../LoginStore';
import config from '../../config';

export class ServerInstance {
    public constructor(
        public connectorId: number,
        public serverName: string,
        public running: boolean,
        public version: string,
        public isSnapshot: boolean,
        public logs: string | null = null
    ) {
    }
}

export class ServersState {
    public servers: Array<ServerInstance> = [];
}

export default <Module<ServersState, LoginState>> {
    namespaced: true,
    state: () => new ServersState(),
    mutations: <MutationTree<ServersState>> {
        assignServers(state: ServersState, payload: any) {
            state.servers = [...state.servers.filter(e => e.connectorId !== payload.connectorId), ...payload.servers];
        },
        refreshLogs(state: ServersState, payload: any) {
            const server = state.servers.find(sr => payload.name === sr.serverName);
            if (server) {
                server.logs = payload.logs;
            }
        }
    },
    actions: <ActionTree<ServersState, LoginState>> {
        async refreshServers(context: ActionContext<ServersState, LoginState>, payload: any) {
            const params = payload?.name;
            const response = await axios.get(config.baseUrl + "/api/server/available_servers/" + payload.id, {
                params: params
            });
            if (response.status === 200) {
                const data = <Array<any>>response.data.data;
                let servers: ServerInstance[] = [];
                data.forEach(server => {
                    servers.push(new ServerInstance(
                        payload.id,
                        server.instance.server_name,
                        server.running,
                        server.instance.version.id,
                        server.instance.version.is_snapshot
                    ));
                });
                context.commit("assignServers", { servers, connectorId: payload.id });
            }
        },
        async refreshLogs(context: ActionContext<ServersState, LoginState>, { name, id }) {
            const response = await axios.get(config.baseUrl + `/api/server/get_logs/${id}/${name}`);
            if (response.status === 200) {
                context.commit("refreshLogs", { name, logs: response.data.log });
            }
        }
    }
}