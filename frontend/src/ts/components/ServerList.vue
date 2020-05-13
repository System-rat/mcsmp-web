<template>
    <div class="col-12">
        <div class="m-auto text-center">
            <div class="spinner-border text-primary" v-if="loading"></div>
        </div>
        <button v-if="!loading" class="btn btn-primary" @click="refreshServers"> Refresh list </button>
        <div v-if="servers.length !== 0 && loading === false">
            <div class="card" v-for="server of servers" :key="server.serverName">
                <div class="card-header">
                    {{ server.serverName }}
                    <span class="text-monospace alert-info"> Status: {{ server.running ? "running" : "stopped" }} </span>
                </div>
                <div class="card-body">
                    <p> Version: {{ server.version }} <span v-if="server.isSnapshot" class="warning"> snapshot </span></p>
                    <button class="btn btn-primary" @click="refreshLogs(server.serverName)"> Refresh logs </button>
                    <pre><code v-if="server.logs">
<span class="border">{{ server.logs }}</span>
                    </code></pre>
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

        get servers(): Array<ServerInstance> {
            return this.$store.state.servers.servers;
        }

        async refreshServers() {
            this.loading = true;
            await new Promise(resolve => setTimeout(resolve, 2000));
            await this.$store.dispatch("servers/refreshServers");
            this.loading = false;
        }

        async refreshLogs(name: string) {
            await this.$store.dispatch("servers/refreshLogs", { name });
        }

        async created() {
            await this.refreshServers();
        }
    }
</script>

<style scoped>

</style>