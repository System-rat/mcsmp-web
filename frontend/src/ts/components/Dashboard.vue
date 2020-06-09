<template>
    <div class="row">
        <connector-list class="col-3" v-if="isLoggedIn"></connector-list>
        <server-list class="col-9" v-if="isLoggedIn && $store.state.connectors.currentConnector"></server-list>
    </div>
</template>

<script lang="ts">
    import Component from 'vue-class-component';
    import Vue from 'vue';
    // @ts-ignore
    import ServerList from './ServerList';
    // @ts-ignore
    import ConnectorList from './ConnectorList';
    import {mapGetters} from "vuex";

    @Component({
        components: {
            ServerList,
            ConnectorList
        },
        computed: {
            ...mapGetters([
                "isLoggedIn",
                "readableName"
            ])
        }
    })
    export default class Dashboard extends Vue {

    }
</script>

<style scoped>

</style>