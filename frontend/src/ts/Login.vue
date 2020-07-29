<template>
        <div>
            <button type="button" class="btn-primary btn" data-toggle="modal" data-target="#loginModal">
                Login
            </button>
            <div class="modal fade col-12" id="loginModal" ref="modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Login</h5>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input class="form-control" v-model="username" name="username" id="username" type="text" />
                                </div>
                                <div class="form-group">
                                    <label for="authenticator">Password</label>
                                    <input class="form-control" v-model="authenticator" name="authenticator" id="authenticator" type="password" />
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"> Cancel </button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal" @click="login"> Login </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from "vue-class-component";
    import { mapGetters } from "vuex";
    import $ from 'jquery';
import config from './config';

    @Component({
        computed: {
            ...mapGetters(["isLoggedIn"])
        }
    })
    export default class Login extends Vue {
        private username: string = "";
        private authenticator: string = "";

        async login() {
            await this.$store.dispatch("login", {
                username: this.username,
                authenticator: this.authenticator
            });
            config.eventBus.$emit('notify-info', "Logged in.");
            this.username = "";
            this.authenticator = "";
        }

        get thing(): string {
            return "nice";
        }
    }
</script>

<style scoped>
</style>