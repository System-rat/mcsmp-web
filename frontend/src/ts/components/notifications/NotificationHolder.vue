<template>
    <div id="notification-holder">
        <transition-group name="notification" tag="div">
            <div class="toast show fade" :class="toastClass(notification.type)" @click="deleteNotification(notification)" v-for="notification of notificationStack" v-bind:key="notification.hash">
                <div class="toast-header">
                    <span class="mr-auto"> {{ notification.title }} </span>
                    <small> {{ formatTime(notification.timeReceived) }} </small>
                </div>
                <div class="toast-body">
                    {{ notification.text }}
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script lang="ts">
import Vue from 'vue'
import Component from 'vue-class-component';
import { Notification, NotificationType } from '../../models/notification';
import config from '../../config';
import $ from 'jquery';

@Component
export default class NotificationHolder extends Vue {
    private notificationStack: Array<Notification> = [];

    public pushNotification(notif: Notification, duration?: number) {
        this.notificationStack.push(notif);

        if (duration) {
            setTimeout(() => {
                this.deleteNotification(notif);
                this.$forceUpdate();
            }, duration);
        }
    }

    public deleteNotification(ref: Notification) {
        const index = this.notificationStack.indexOf(ref);
        if (index > -1) {
            this.notificationStack.splice(index, 1);
        }
    }

    toastClass(notificationType: NotificationType): any {
        switch(notificationType) {
            case NotificationType.Error: return "text-white bg-danger";
            case NotificationType.Info: return "text-white bg-primary";
        }
    }

    formatTime(date: Date): string {
        return `at ${date.getHours()}:${date.getMinutes()}`
    }

    created() {
        config.eventBus.$on('notify-error', this.errorListener);
        config.eventBus.$on('notify-info', this.infoListener);
    }

    beforeDestroy() {
        config.eventBus.$off('notify-error', this.errorListener);
        config.eventBus.$off('notify-info', this.infoListener);
    }

    private infoListener(infoText: string) {
        this.pushNotification(new Notification(
            "Info",
            infoText,
            NotificationType.Info
        ), 3000);
    }

    private errorListener(errorText: string) {
        this.pushNotification(new Notification(
            "Error",
            errorText,
            NotificationType.Error
        ), 3000);
    }
}
</script>

<style lang="scss" scoped>
    #notification-holder {
        position: fixed;
        padding: 50px;
        bottom: 0;
        right: 0;
        z-index: 1000;
    }

    .toast {
        min-width: 200px;
    }

    .notification-enter, .notification-leave-to {
        opacity: 0;
        transform: translateX(100px);
    }

    .toast-header {
        background-color: rgba(0, 0, 0, 0.1);
        color: inherit;
    }

    .notification-enter-active, .notification-leave-active {
        transition: all 0.5s;
    }

    .notification-move {
        transition: transform 0.3s;
    }
</style>