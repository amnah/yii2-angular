
<template>
    <div>
        <div v-if="loaded">

            <h1>Account</h1>

            <div class="success" v-if="success">
                <div class="alert alert-success">
                    <p>{{ success }}</p>
                </div>
            </div>

            <form id="account-form" class="form-horizontal" role="form" @submit.prevent="submit">

                <div class="form-group" v-bind:class="{'has-error': errors.currentPassword}" v-if="hasPassword">
                    <label class="col-lg-2 control-label" for="account-form-currentPassword">Current Password</label>
                    <div class="col-lg-3">
                        <input type="password" id="account-form-currentPassword" class="form-control" v-model.trim="form.currentPassword">
                    </div>
                    <div class="col-log-7">
                        <p class="help-block help-block-error" v-if="errors.currentPassword">{{ errors.currentPassword[0] }}</p>
                    </div>
                </div>
                <hr v-if="hasPassword">
                <div class="form-group" v-bind:class="{'has-error': errors.email}">
                    <label class="col-lg-2 control-label" for="account-form-email">Email</label>
                    <div class="col-lg-3">
                        <input type="text" id="account-form-email" class="form-control" v-model.trim="form.email">
                    </div>
                    <div class="col-log-7">
                        <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-offset-2 col-lg-10">
                        <p class="small" v-if="userToken">Pending email confirmation: [ {{ userToken.data }} ]</p>
                        <p class="small" v-if="userToken">
                            <a @click="resend">Resend</a> / <a @click="cancel">Cancel</a>
                        </p>
                        <p class="small" v-if="!userToken">Changing your email requires email confirmation</p>
                    </div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': errors.username}">
                    <label class="col-lg-2 control-label" for="account-form-username">Username</label>
                    <div class="col-lg-3">
                        <input type="text" id="account-form-username" class="form-control" v-model.trim="form.username">
                    </div>
                    <div class="col-log-7">
                        <p class="help-block help-block-error" v-if="errors.username">{{ errors.username[0] }}</p>
                    </div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': errors.newPassword}">
                    <label class="col-lg-2 control-label" for="account-form-newPassword">New Password</label>
                    <div class="col-lg-3">
                        <input type="password" id="account-form-newPassword" class="form-control" placeholder="(may be left empty)" v-model.trim="form.newPassword">
                    </div>
                    <div class="col-log-7">
                        <p class="help-block help-block-error" v-if="errors.newPassword">{{ errors.newPassword[0] }}</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <button type="submit" class="btn btn-primary" :disabled="submitting">Save</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
import {setPageTitle} from '../functions.js'
import {get, post, reset, process} from '../api.js'
export default {
    name: 'account',
    beforeCreate: function() {
        setPageTitle('Account')
        const vm = this
        get('user').then(function(data) {
            vm.loaded = true
            if (data.success) {
                vm.form = data.success.user
                vm.userToken = data.success.userToken
                vm.hasPassword = data.success.hasPassword
            }
        });
    },
    data: function() {
        return {
            success: false,
            submitting: false,
            errors: {},
            loaded: false,
            form: {},
            userToken: null,
            hasPassword: false
        }
    },
    methods: {
        submit: function(e) {
            const vm = this
            reset(vm)
            post('user', vm.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.success = 'Account saved';
                    vm.userToken = data.success.userToken
                    vm.$store.dispatch('renewLogin', true)
                }
            });
        },
        resend: function(e) {
            const vm = this
            reset(vm)
            post('user/change-resend').then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.success = 'Email resent';
                }
            });
        },
        cancel: function(e) {
            const vm = this
            reset(vm)
            post('user/change-cancel').then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.userToken = null
                    vm.success = 'Email change cancelled';
                }
            });
        }
    }
}
</script>