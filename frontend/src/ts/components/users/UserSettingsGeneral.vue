<template>
    <div class="container-fluid pb-3">
        <div class="row mt-2 mb-3">
            <h5> General user account settings </h5>
        </div>
        <div class="row border mb-3">
            <div class="col-8">
                <h5 class="d-block"> Account info </h5>
                <table class="table">
                    <tr>
                        <td><span class="pr-3"> Username </span></td>
                        <td><input class="form-control" disabled :value="username"></td>
                    </tr>
                    <tr>
                        <td><span class="pr-3"> Display name </span></td>
                        <td><input class="form-control" disabled :value="displayName"></td>
                    </tr>
                    <tr>
                        <td><span class="pr-3"> Account type </span></td>
                        <td><span> {{ accountTypeString(accountType) }} </span></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row border">
            <div class="col-6">
                <h5 class="d-block"> Change password </h5>
                <form ref="form">
                    <div class="form-group">
                        <label for="old-pass"> Old password </label>
                        <input v-model="oldPass" class="form-control" type="password" id="old-pass" required>
                        <span v-if="oldPassEqualNew" class="text-danger"> New password must not be equal to new password </span>
                    </div>
                    <div class="form-group">
                        <label for="new-pass"> New password </label>
                        <input v-model="newPass" class="form-control" type="password" id="new-pass" required>
                        <label for="new-pass-r"> Repeat password </label>
                        <input v-model="newPassR" class="form-control" type="password" id="new-pass-r" required>
                        <span v-if="!newPassValid" class="text-danger"> Passwords must match </span>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" @click="changePassword()" :disabled="!isValid"> Change password </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue'
import Component from 'vue-class-component';
import config from '../../config';
import { mapState } from 'vuex';
import { AccountType } from '../../store/LoginStore';

@Component({
    computed: {
        ...mapState([
            'username',
            'displayName',
            'accountType'
        ])
    }
})
export default class UserSettingsGeneral extends Vue {
    private oldPass: string = '';
    private newPass: string = '';
    private newPassR: string = '';
    
    get newPassValid(): boolean {
        return this.newPass === this.newPassR || this.newPass === '';
    }

    get oldPassEqualNew(): boolean {
        return this.newPass === this.oldPass && this.oldPass !== '';
    }

    accountTypeString(at: AccountType): string {
        return AccountType[at];
    }

    get isValid(): boolean {
        return !this.oldPassEqualNew
            && this.newPassValid
            && this.newPass !== ''
            && this.oldPass !== '';
    }

    clear() {
        (<any>this.$refs["form"]).reset();
    }

    async changePassword() {
        if (!this.isValid) {
            return;
        }

        try {
            await this.$store.dispatch('changePassword', {
                old_password: this.oldPass,
                new_password: this.newPass
            });
            config.eventBus.$emit('notify-info', 'Password changed');
            this.clear();
        } catch(err) {
            if ('response' in err) {
                config.eventBus.$emit('notify-error', err.response.data?.message);
            }
        }
    }
}
</script>