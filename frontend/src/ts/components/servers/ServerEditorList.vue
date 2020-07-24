<template>
    <div class="ml-1 mr-1">
        <div class="col-12 col-md-3">
            <div class="w-100 shadow border p-1">
                <button v-if="selectedConnector !== ''" class="btn" @click="selectedConnector = ''"> &lt; </button>
                <div v-if="loading" class="progress"><div class="progress-bar w-100 progress-bar-striped progress-bar-animated"></div></div>
                <ul class="list-group list-group-flush w-100" v-if="connectors && selectedConnector === ''">
                    <li class="list-group-item p-0 w-100" v-for="connector of connectors" v-bind:key="connector.id">
                        <a href="#" class="d-inline-block p-3 m-0 text-decoration-none w-100 text-left" @click="selectConnector(connector)">
                            <span @click="selectedConnector = connector" class="text-reset">
                                {{ connector.host + ':' + connector.port + '/' + (connector.subDirectory == null ? '' : connector.subDirectory)  }}
                            </span>
                            <span class="badge" :class="{
                                'badge-success': connector.status === 'alive',
                                'badge-warning': connector.status.startsWith('probably'),
                                'badge-danger': connector.status === 'dead'
                            }"> {{ connector.status }} </span>
                        </a>
                    </li>
                    <li class="list-group-item p-0 text-center">
                        <a href="#" class="p-3 m-0 text-decoration-none d-inline-block w-100" @click="toggleCreateConnectorModal">
                            <span class="fa fa-plus text-info"></span>
                        </a>
                    </li>
                </ul>
                <ul class="list-group list-group-flush w-100" v-if="selectedConnector !== ''">
                    <li class="list-group-item p-0 w-100" v-for="server of getServersByConnector(selectedConnector.id)" v-bind:key="server.serverName">
                        <a class="d-inline-block p-3 m-0 text-decoration-none w-100 text-left" href="#" @click="selectedServer = server">{{ server.serverName }} </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-12 col-md-9 shadow border p-1" v-if="selectedServer !== ''">
            <server-editor :server="selectedServer"></server-editor>
        </div>
        <div class="modal show fade" id="create-connector-modal">
            <form class="modal-dialog" @submit="createConnector">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="alert alert-danger" v-if="hasError">
                            And error occurred.
                        </div>
                        <div class="form-group">
                            <label for="connector-host-field">Host</label>
                            <input v-model="newConnector.host" class="form-control" id="connector-host-field" required>
                        </div>
                        <div class="form-group">
                            <label for="connector-port-field">Port</label>
                            <input v-model="newConnector.port" class="form-control" id="connector-port-field" type="number">
                        </div>
                        <div class="form-group">
                            <label for="connector-token-field">Token</label>
                            <input v-model="newConnector.token" class="form-control" id="connector-token-field">
                        </div>
                        <div class="form-group">
                            <label for="connector-subdir-field">Subdirectory</label>
                            <input v-model="newConnector.sub_directory" class="form-control" id="connector-subdir-field">
                        </div>
                        <button class="btn btn-secondary" @click="toggleCreateConnectorModal()">Cancel</button>
                        <button class="btn btn-primary">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from "vue-class-component";
    import {mapState, mapGetters} from "vuex";
    import {Connector} from "../../store/servers/ConnectorStore";
    import $ from 'jquery';
    import { ServerInstance } from '../../store/servers/ServerStore';
    import ServerEditor from './ServerEditor.vue';

    @Component({
        computed: {
            ...mapState('connectors', [
                'connectors'
            ]),
            ...mapGetters('servers', {
                getServersByConnector: 'getServersByConnector'
            })
        },
        components: {
            ServerEditor
        }
    })
    export default class ServerEditorList extends Vue {
        private selectedConnector: Connector | string = '';
        private selectedServer: ServerInstance | string = '';
        private newConnector = {
            host: '',
            port: 1337,
            token: '',
            sub_directory: ''
        }
        private isCreating = false;
        private hasError = false;
        private loading = false;

        toggleCreateConnectorModal() {
            (<any>$('#create-connector-modal')).modal('toggle');
            this.newConnector = {
                host: '',
                port: 1337,
                token: '',
                sub_directory: ''
            };
            this.hasError = false;
        }

        async selectConnector(con: Connector) {
            this.loading = true;
            this.selectedConnector = con;
            await this.$store.dispatch('servers/refreshServers', {id: this.selectedConnector.id});
            this.loading = false;
        }

        activated() {
            this.selectedConnector = '';
            this.selectedServer = '';
        }

        async createConnector() {
            this.isCreating = true;
            if (await this.$store.dispatch('connectors/createConnector', this.newConnector)) {
                this.toggleCreateConnectorModal();
            } else {
                this.hasError = true;
            }
            this.isCreating = false;
        }
    }
</script>

<style scoped>
    .list-group-item:hover {
        background-color: lightgray;
        transition: background-color 80ms ease;
    }
</style>