<template>
    <div>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="d-inline align-middle">Connectors</h4>
                <button v-if="!loading" class="btn btn-link" @click="refresh"><i class="fas fa-redo"></i></button>
            </div>
            <div class="card-body">
                <div class="m-auto text-center">
                    <div class="spinner-border text-primary" v-if="loading"></div>
                </div>
                <ul class="list-group list-group-flush" v-if="connectors.length !== 0 && !loading">
                    <li class="list-group-item" v-for="connector of connectors" :key="connector.id">
                        <span class="text-info">Connector: {{connector.host}}:{{connector.port}}</span><span v-if="connector.subDirectory" class="text-secondary">/{{connector.subDirectory}}</span>
                        <br />
                        Status <span :class="statusClass(connector)">{{connector.status}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue'
    import Component from "vue-class-component";
    import {Connector} from "../store/servers/ConnectorStore";

    @Component
    export default class ConnectorList extends Vue {
        private loading: boolean = false;

        async created() {
            await this.refresh();
        }

        statusClass(connector: Connector) {
            return {
                'text-danger': connector.status === "dead",
                'text-success': connector.status === "alive",
                'text-warning': connector.status === "probably alive i really don't know lmao"
            };
        }

        get connectors(): Array<Connector> {
            return this.$store.state.connectors.connectors;
        }

        public async refresh() {
            this.loading = true;
            await this.$store.dispatch("connectors/getConnectors");
            this.loading = false;
        }
    }
</script>

<style scoped>

</style>