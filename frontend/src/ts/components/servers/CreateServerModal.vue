<template>
    <div class="modal fade" id="server-create-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Create server </h5>
                    <button class="close" type="button" :disabled="creating" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="progress m-1" v-if="creating">
                            <div class="progress-bar progress-bar-striped progress-bar-animated w-100"> Creating... </div>
                        </div>
                        <div class="form-group">
                            <label for="server-name"> Server name </label>
                            <input type="text" class="form-control" id="server-name" name="server-name" v-model="serverName">
                        </div>
                        <a href="#" class="text-sm" v-if="!showAdvanced" @click="showAdvanced = true"> Show advanced </a>
                        <div class="form-group" v-if="showAdvanced">
                            <label for="server-version"> Server version </label>
                            <version-picker class="custom-control custom-select" id="server-version" v-model="serverVersion" :showSnapshots="showSnapshots"></version-picker>
                        </div>
                        <div class="form-group" v-if="showAdvanced">
                            <label for="show-snapshots"> Show snapshots? </label>
                            <input type="checkbox" name="show-snapshots" id="show-snapshots" v-model="showSnapshots">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" :disabled="creating" data-dismiss="modal"> Cancel </button>
                    <button type="button" class="btn btn-primary" :disabled="creating" @click="create()"> Create </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue'
import Component from 'vue-class-component';
import VersionPicker from './VersionPicker.vue';
import $ from 'jquery';
import { Prop } from 'vue-property-decorator';
import config from '../../config';

@Component({
    components: {
        VersionPicker
    }
})
export default class CreateServerModal extends Vue {
    private serverName: string = "";
    private serverVersion: string = "";
    @Prop({ required: true, default: 0})
    public connectorId!: number;
    private showSnapshots = false;
    private showAdvanced = false;
    private creating = false;

    public toggle() {
        (<any>$('#server-create-modal')).modal('toggle');
        this.serverName = "";
        this.serverVersion = "";
        this.showAdvanced = false;
        this.showSnapshots = false;
    }

    async create() {
        this.creating = true;
        try {
            await this.$store.dispatch('servers/createServer', {
                serverName: this.serverName,
                serverVersion: this.serverVersion,
                connectorId: this.connectorId
            });
        } catch(err) {
            if ('response' in err) {
                config.eventBus.$emit('notify-error', err.response.data?.message);
            }
        }
        this.creating = false;
        this.toggle();
    }
}
</script>

<style lang="scss" scoped>

</style>