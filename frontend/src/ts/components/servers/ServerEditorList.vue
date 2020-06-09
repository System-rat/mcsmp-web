<template>
    <div class="container">
        <div class="col-4-lg">
            <ul class="list-group" v-if="connectors">
                <li class="list-group-item position-relative" v-for="connector of connectors">
                    <a class="stretched-link text-reset text-decoration-none" href="#">
                        {{ connector.host + ':' + connector.port + '/' + connector.subDirectory }}
                    </a>
                    <span class="badge" :class="{
                        'badge-success': connector.status === 'alive',
                        'badge-warning': connector.status.startsWith('probably'),
                        'badge-danger': connector.status === 'dead'
                    }"> {{ connector.status }} </span>
                </li>
                <li class="list-group-item text-center">
                    <button class="btn w-100">
                        <span class="fa fa-plus text-info"></span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="col-8-lg" v-if="typeof(selectedConnector) !== 'string'">

        </div>
        <div class="modal">

        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from "vue-class-component";
    import {mapGetters, mapState} from "vuex";
    import {Connector} from "../../store/servers/ConnectorStore";

    @Component({
        computed: {
            ...mapState('connectors', [
                'connectors'
            ])
        }
    })
    export default class ServerEditorList extends Vue {
        private selectedConnector: Connector | string = '';


    }
</script>

<style scoped>

</style>