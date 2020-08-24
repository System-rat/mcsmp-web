<template>
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="alert-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> {{ currentAlert.title }}</h5>
            </div>
            <div class="modal-body" :class="textClass()">
                <div class="progress m-1" v-if="working">
                    <div class="progress-bar progress-bar-striped progress-bar-animated w-100" :class="bgClass()"> Working... </div>
                </div>
                {{ currentAlert.message }}
            </div>
            <div class="modal-footer" v-if="!currentAlert.requireConfirm">
                <button type="button" class="btn btn-primary" @click="confirm()"> Ok </button>
            </div>
            <div class="modal-footer" v-if="currentAlert.requireConfirm">
                <button type="button" class="btn btn-secondary" @click="dismiss()"> Cancel </button>
                <button type="button" class="btn" :class="confirmClass()" @click="confirm()"> Confirm </button>
            </div>
        </div>
    </div>
</div>
</template>

<script lang="ts">
import Vue from 'vue'
import Component from 'vue-class-component';
import { AlertMessage, AlertType } from '../../models/alert';
import $ from 'jquery';
import config from '../../config';

@Component
export default class AlertHolder extends Vue {
    private currentAlert: AlertMessage = new AlertMessage(""); 
    private alerts: AlertMessage[] = [];
    private working = false;
    
    private toggleModal() {
        (<any>$('#alert-modal')).modal('toggle');
    }
    
    public alert(message: AlertMessage) {
        this.alerts = [...this.alerts, message];
        this.toggleModal();
        this.showAlert();
    }

    textClass() {
        switch(this.currentAlert.alertType) {
            case AlertType.Info: return "";
            case AlertType.Debug: return "text-info";
            case AlertType.Warning: return "text-warrning";
            case AlertType.Critical: return "text-danger";
        }
    }

    confirmClass() {
        switch(this.currentAlert.alertType) {
            case AlertType.Info: return "btn-primary";
            case AlertType.Debug: return "btn-info";
            case AlertType.Warning: return "btn-warrning";
            case AlertType.Critical: return "btn-danger";
        }
    }

    bgClass () {
        switch(this.currentAlert.alertType) {
            case AlertType.Info: return "bg-primary";
            case AlertType.Debug: return "bg-info";
            case AlertType.Warning: return "bg-warrning";
            case AlertType.Critical: return "bg-danger";
        }
    }

    showAlert() {
        const notif = this.alerts.shift()
        if (notif !== undefined) {
            this.currentAlert = notif;
        } else {
            this.toggleModal();
        }
    }

    async dismiss() {
        await this.work(false);
        this.showAlert();
    }

    async work(res: boolean) {
        this.working = true;
        await this.currentAlert.onConfirm?.(res);
        this.working = false;
    }

    async confirm() {
        await this.work(true);
        this.showAlert();
    }

    created() {
        config.eventBus.$on('alert', this.alert);
    }

    beforeDestroy() {
        config.eventBus.$off('alert', this.alert);
    }
}
</script>

<style lang="scss" scoped>

</style>