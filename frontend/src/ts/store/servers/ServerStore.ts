import axios from 'axios';
import {ActionContext, Action} from 'vuex';
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
        public logs: string | null = null,
        public properties: Record<string, any> | null = null
    ) {
    }
}

export class ServerInstanceEdit {
    public version: string;
    public isSnapshot: boolean;
    public properties: Record<string, any> = {};

    public constructor(instance: ServerInstance) {
        this.isSnapshot = instance.isSnapshot;
        this.version = instance.version;
        for (let prop in instance.properties) {
            this.properties[prop] = instance.properties[prop];
        }
    }
}

type ServerContext = ActionContext<ServersState, LoginState>;

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
        },
        setProperties(state: ServersState, payload: any) {
            const server = state.servers.find(sr => sr.serverName === payload.name);
            if (server) {
                server.properties = payload.properties;
            }
        },
        setServerInfo(state: ServersState, payload: any) {
            const server = state.servers.find(sr => sr.serverName === payload.name);
            if (server) {
                server.version = payload.version || server.version;
                server.isSnapshot = payload.is_snapshot || server.isSnapshot;
            }
        }
    },
    getters: <GetterTree<ServersState, LoginState>> {
        getServersByConnector(state: ServersState) : ((connectorId: number) => Array<ServerInstance>) {
            return (connectorId: number) : Array<ServerInstance> => {
                return state.servers.filter(el => el.connectorId === connectorId);
            }
        },
        test(state: ServersState) : number {
            return state.servers[0].connectorId;
        }
    },
    actions: <ActionTree<ServersState, LoginState>> {
        async refreshServers(context: ServerContext, payload: any) {
            const params = payload?.name;
            const response = await axios.get(config.baseUrl + "/api/server/available_servers/" + payload.id, {
                params: params
            });
            if (response.status === 200) {
                const data = <Array<any>>response.data.data;
                let servers: ServerInstance[] = [];
                let serverPropertiesPromises: Array<Promise<any>> = [];
                data.forEach(server => {
                    servers.push(new ServerInstance(
                        payload.id,
                        server.instance.server_name,
                        server.running,
                        server.instance.version.id,
                        server.instance.version.is_snapshot
                    ));
                    serverPropertiesPromises.push(context.dispatch("refreshProperties", { name: server.instance.server_name, id: payload.id }));
                });
                context.commit("assignServers", { servers, connectorId: payload.id });
                await Promise.all(serverPropertiesPromises);
            }
        },
        async refreshLogs(context: ServerContext, { name, id }) {
            const response = await axios.get(config.baseUrl + `/api/server/get_logs/${id}/${name}`);
            if (response.status === 200) {
                context.commit("refreshLogs", { name, logs: response.data.log });
            }
        },
        async refreshProperties(context: ServerContext, payload: any) {
            const response = await axios.get(config.baseUrl + `/api/server/get_properties/${payload.id}/${payload.name}`);
            if (response.status === 200) {
                context.commit("setProperties", { name: payload.name, properties: response.data.properties });
            }
        },
        async updateProperties(context: ServerContext, { name, id, properties }): Promise<boolean> {
            const params = new URLSearchParams();
            params.append('properties', JSON.stringify(properties));
            const response = await axios.patch(config.baseUrl + `/api/server/update_properties/${id}/${name}`, params);
            if (response.status === 200) {
                context.commit("setProperties", { name, properties });
                return true;
            } else {
                return false;
            }
        },
        async downloadLatest(context: ServerContext, { name, id, snapshot }) {
            const params = new URLSearchParams();
            if (snapshot) {
                params.append('is_snapshot', snapshot);
            }
            const response = await axios.post(config.baseUrl + `/api/server/download_latest/${id}/${name}`, params);
            if (response.status === 200) {
                context.commit("setServerInfo", { name, ...response.data });
            }
        }
    }
}