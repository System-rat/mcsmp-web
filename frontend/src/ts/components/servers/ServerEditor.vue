<template>
    <div class="container-fluid p-2">
        <div class="d-block">
            <h5> {{ server.serverName }} </h5>
            <hr />
        </div>
        <div class="row">
            <form ref="form" class="col-12 col-md-5">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"> Server version </span>
                        </div>
                        <input class="form-control" type="text" id="server-version-field" v-model="serverForEdit.version" />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                Snapshot?&nbsp;
                                <input type="checkbox" id="snapshot-field" v-model="serverForEdit.isSnapshot" :value="serverForEdit.isSnapshot" />
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" @click="apply()" type="button"> Apply </button>
                <button class="btn btn-secondary" @click="reset()" type="button"> Revert </button>
            </form>
            <div class="col-12 col-md-7 p-1">
                <h6> Properties </h6>
                <hr />
                <div class="border">
                    <ul class="list-group-flush">
                        <li class="list-group-item">
                            <div class="d-block">
                                <span class="align-middle"> Port </span><input type="number" class="form-control" value="1337" />
                            </div>
                        </li>
                    </ul>
                </div>
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

        @Watch('server')
        onServerPropertyUpdated(_value: ServerInstance, _oldValue: ServerInstance) {
            this.reset();
        }

        reset() {
            this.serverForEdit = new ServerInstanceEdit(this.server);
        }

        apply() {

        }
    }
</script>

<style scoped>

</style>