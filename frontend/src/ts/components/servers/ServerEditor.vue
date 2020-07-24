<template>
    <div class="container-fluid p-2">
        <div class="d-block">
            <h5> {{ server.serverName }} </h5>
            <hr />
        </div>
        <div class="row">
            <form ref="form" class="col-12 col-md-5">
                <div class="form-group">
                    <h5> Status </h5>
                    <div class="d-flex flex-row align-items-center">
                        <span :class="{ 'text-success': server.running, 'text-danger': !server.running }" class="mx-1">{{ server.running ? 'running' : 'stopped' }}</span>
                        <button type="button" @click="toggleServer()" class="mx-1 btn" :class="{ 'btn-success': !server.running, 'btn-danger': server.running }">
                            {{ server.running ? 'Stop' : 'Start' }}
                        </button>
                        <button type="button" class="mx-1 btn btn-warning" @click="restartServer()">Restart</button>
                        <div class="mx-1 spinner-border text-primary" v-if="togglingServer"></div>
                    </div>
                    <h5> Versioning </h5>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> Server version </span>
                            </div>
                            <version-picker class="custom-control custom-select" id="server-version-field" @input="serverDirty = true" v-model="serverForEdit.version" :showSnapshots="serverForEdit.isSnapshot" />
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    Snapshot?&nbsp;
                                    <input type="checkbox" id="snapshot-field" v-model="serverForEdit.isSnapshot" @input="serverDirty = true" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-lg-6 p-1">
                                    <button :disabled="isDownloading" type="button" class="btn btn-primary w-100" @click="downloadLatest(false)">Download latest stable</button>
                                </div>
                                <div class="col-12 col-lg-6 p-1">
                                    <button :disabled="isDownloading" type="button" class="btn btn-warning w-100" @click="downloadLatest(true)" data-toggle="tooltip" data-placement="top" title="Could be very unstable">Download latest snapshot</button>
                                </div>
                            </div>
                        </div>
                        <div class="progress m-1" v-if="isDownloading">
                            <div class="progress-bar progress-bar-striped progress-bar-animated w-100"> Downloading... </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="col-12 col-md-7 p-1">
                <h6> Properties </h6>
                <hr />
                <div v-if="isLoading" class="progress"><div class="progress-bar w-100 progress-bar-striped progress-bar-animated"></div></div>
                <div class="border">
                    <ul class="list-group-flush" id="properties-list">
                        <li class="list-group-item" v-for="(prop, propKey) in serverForEdit.properties" v-bind:key="propKey">
                            <label :for="propKey + '-id'" class="text-info">{{ propKey }} </label>
                            <input class="w-100" :id="propKey + '-id'" type="text" v-model="serverForEdit.properties[propKey]" @input="propertiesDirty = true" />
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-12" v-if="isDirty">
                <button class="btn btn-primary" @click="apply()" type="button"> Apply </button>
                <button class="btn btn-secondary" @click="reset()" type="button"> Revert </button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import { Prop, Watch } from "vue-property-decorator";
    import { ServerInstance, ServerInstanceEdit } from "../../store/servers/ServerStore";
    import VersionPicker from './VersionPicker.vue';

    @Component({
        components: {
            VersionPicker
        }
    })
    export default class ServerEditor extends Vue {
        @Prop({required: true})
        private server!: ServerInstance;
        private serverForEdit: ServerInstanceEdit = new ServerInstanceEdit(this.server);

        private serverDirty: boolean = false;
        private propertiesDirty: boolean = false;
        private isDownloading: boolean = false;
        private isLoading: boolean = false;
        private togglingServer: boolean = false;

        @Watch('server')
        onServerPropertyUpdated(_value: ServerInstance, _oldValue: ServerInstance) {
            this.reset();
        }

        get isDirty() {
            return this.serverDirty || this.propertiesDirty;
        }

        reset() {
            this.serverForEdit = new ServerInstanceEdit(this.server);
            this.propertiesDirty = false;
            this.serverDirty = false;
        }

        mounted() {
            // why is this still a thing?
            $(function () {
                (<any>$('[data-toggle="tooltip"]')).tooltip();
            });
        }

        async downloadLatest(isSnapshot: boolean) {
            this.isDownloading = true;
            try {
                await this.$store.dispatch("servers/downloadLatest", {
                    name: this.server.serverName,
                    id: this.server.connectorId,
                    snapshot: isSnapshot
                });
            } catch (err) {
                window.alert(err);                
            }
            this.isDownloading = false;
            this.reset();
        }

        async toggleServer() {
            this.togglingServer = true;
            if (this.server.running) {
                await this.$store.dispatch("servers/stopServer", { name: this.server.serverName, id: this.server.connectorId });
            } else {
                await this.$store.dispatch("servers/startServer", { name: this.server.serverName, id: this.server.connectorId });
            }
            this.togglingServer = false;
        }

        async restartServer() {
            this.togglingServer = true;
            await this.$store.dispatch("servers/stopServer", { name: this.server.serverName, id: this.server.connectorId });
            await this.$store.dispatch("servers/startServer", { name: this.server.serverName, id: this.server.connectorId });
            this.togglingServer = false;
        }

        async apply() {
            let res = false;
            if (this.propertiesDirty) {
                res = await this.$store.dispatch("servers/updateProperties", {
                    name: this.server.serverName,
                    id: this.server.connectorId,
                    properties: this.serverForEdit.properties
                });
            }

            if (res) {
                this.reset();
            } else {
                alert("An error happened");
            }
        }
    }
</script>

<style scoped lang="scss">
    #properties-list {
        max-height: 500px;
        overflow-y: scroll;

        input {
            border-style: none;
            margin-bottom: 1px;
            outline: none;
            transition: 0.3s;

            &:focus {
                margin-bottom: 0px;
                border-bottom: solid 1px;
            }
        }
    }
    select {
        -moz-appearance: none;
        -webkit-appearance: none;
    }
</style>