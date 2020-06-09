<template>
    <div>
        <div class="card accordion" id="serverList">
            <div class="card-header d-flex justify-content-between">
                <h4 class="d-inline">Servers on connector {{ $store.state.connectors.currentConnector.host }}</h4>
                <button v-if="!loading" class="btn btn-link" @click="refreshServers"><i class="fas fa-redo"></i></button>
            </div>

            <div class="card-body">
                <div class="m-auto text-center">
                    <div class="spinner-border text-primary" v-if="loading"></div>
                </div>

                <div v-if="servers.length !== 0 && loading === false">
                    <ul class="list-group list-group-flush">
                        <li class="text-center list-group-item" v-if="servers.length === 0">
                            <i class="fas fa-question"></i>
                            <span class="text-muted">No servers available</span>
                        </li>

                        <li class="list-group-item" v-for="server of servers" :key="server.serverName">
                            {{ server.serverName }}
                            <span class="text-monospace" :class="[server.running ? 'text-success' : 'text-danger']"> {{ server.running ? "running" : "stopped" }} </span>
                            <p> Version: {{ server.version }} <span v-if="server.isSnapshot" class="warning"> snapshot </span></p>
                            <button class="btn btn-link mb-1" :data-toggle="'collapse'" :data-target="'#logs-' + server.serverName"> Show logs </button>
                            <div :id="'logs-' + server.serverName" class="collapse p-2 border rounded" :data-parent="'#serverList'" >
                                <button class="btn btn-link mb-1" @click="refreshLogs(server.serverName)"><i class="fas fa-redo"></i></button>
                                <div v-if="logsLoading" class="m-auto text-center">
                                    <div class="spinner-border text-primary" v-if="loading"></div>
                                </div>
                                <div v-if="!server.logs && !logsLoading" class="text-center">
                                    <i class="fas fa-not-equal"></i><br />
                                    <span class="text-muted">No logs available</span>
                                </div>
                                <pre><code v-if="server.logs"><span class="border">{{ server.logs }}</span></code></pre>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from "vue-class-component";
    import {ServerInstance} from "../store/servers/ServerStore";
    import {mapActions} from "vuex";

    @Component
    export default class ServerList extends Vue{
        private loading: boolean = false;
        private logsLoading: boolean = false;

        get servers(): Array<ServerInstance> {
            return this.$store.state.servers.servers;
        }

        async refreshServers() {
            this.loading = true;
            await new Promise(resolve => setTimeout(resolve, 2000));
            await this.$store.dispatch("servers/refreshServers",
                { id: this.$store.state.connectors.currentConnector.id });
            this.loading = false;
            this.logsLoading = true;
            let logPromises: Array<Promise<any>> = []
            this.servers.forEach(server => {
                logPromises.push(this.refreshLogs(server.serverName));
            });
            await Promise.all(logPromises);
            this.logsLoading = false;
        }

        async refreshLogs(name: string) {
            await this.$store.dispatch("servers/refreshLogs",
                { name, id: this.$store.state.connectors.currentConnector.id });
        }

        async created() {
            await this.refreshServers();
        }
    }
</script>

<style scoped>

</style>