<template>
    <div class="container-fluid p-2">
        <div class="d-block">
            <h5> {{ server.serverName }} </h5>
            <hr />
        </div>
        <div class="row">
            <form ref="form" class="col-12 col-md-5">
                <div class="form-group">
                    <h5> Versioning </h5>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> Server version </span>
                            </div>
                            <input class="form-control" type="text" id="server-version-field" @input="serverDirty = true" v-model="serverForEdit.version" />
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

    @Component
    export default class ServerEditor extends Vue {
        @Prop({required: true})
        private server!: ServerInstance;
        private serverForEdit: ServerInstanceEdit = new ServerInstanceEdit(this.server);

        private serverDirty: boolean = false;
        private propertiesDirty: boolean = false;
        private isDownloading: boolean = false;

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

        created() {
            // why is this still a thing?
            $(function () {
                (<any>$('[data-toggle="tooltip"]')).tooltip();
            })
        }

        async downloadLatest(isSnapshot: boolean) {
            this.isDownloading = true;
            await this.$store.dispatch("servers/downloadLatest", {
                name: this.server.serverName,
                id: this.server.connectorId,
                snapshot: isSnapshot
            });
            this.isDownloading = false;
            this.reset();
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
</style>