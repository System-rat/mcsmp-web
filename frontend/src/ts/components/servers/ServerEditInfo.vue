<template>
    <div class="container-fluid shadow-sm rounded">
        <div class="row">
            <h1 class="col">{{ serverName }}</h1>
        </div>
        <div class="row">
            <div class="col col-md-6">
                {{ this.version }} <span v-if="this.isSnapshot" class="badge badge-danger">snapshot</span>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from "vue-class-component";
    import {ServerInstance} from "../../store/servers/ServerStore";
    import {Prop} from "vue-property-decorator";

    @Component
    export class ServerEditInfo extends Vue {
        @Prop({required: true})
        public server!: ServerInstance;

        private version = this.server.version;
        private isSnapshot = this.server.isSnapshot;
        private serverName = this.server.serverName;
    }
</script>

<style scoped lang="scss">

</style>