
<template>
    <div>
        <div class="alert alert-success" v-if="successReset">
            <p>Password updated for [ {{ form.email }} ]</p>
            <p><router-link to="/login">Log in here</router-link></p>
        </div>
        <div class="alert alert-danger" v-if="error">{{ error }}</div>

        <form id="reset-form" class="form-horizontal" role="form" v-if="showForm" @submit.prevent="submit">

            <h1>Reset password</h1>
            <div class="form-group">
                <label class="col-lg-2 control-label" for="reset-form-email">Email</label>
                <div class="col-lg-3">
                    <input type="text" id="reset-form-email" class="form-control" disabled v-model.trim="form.email">
                </div>
            </div>
            <div class="form-group" v-bind:class="{'has-error': errors.newPassword}">
                <label class="col-lg-2 control-label" for="reset-form-newPassword">New Password</label>
                <div class="col-lg-3">
                    <input type="password" id="reset-form-newPassword" class="form-control" v-model.trim="form.newPassword">
                </div>
                <div class="col-lg-7">
                    <p class="help-block help-block-error" v-if="errors.newPassword">{{ errors.newPassword[0] }}</p>
                </div>
            </div>
            <div class="form-group" v-bind:class="{'has-error': errors.newPasswordConfirm}">
                <label class="col-lg-2 control-label" for="reset-form-newPasswordConfirm">New Password Confirm</label>
                <div class="col-lg-3">
                    <input type="password" id="reset-form-newPasswordConfirm" class="form-control" v-model.trim="form.newPasswordConfirm">
                </div>
                <div class="col-lg-7">
                    <p class="help-block help-block-error" v-if="errors.newPasswordConfirm">{{ errors.newPasswordConfirm[0] }}</p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-offset-1 col-lg-11">
                    <button type="submit" class="btn btn-primary" :disabled="submitting">Submit</button>
                </div>
            </div>

        </form>
    </div>
</template>

<script>
import {setPageTitle} from '../functions.js'
import {get, post, reset, process} from '../api.js'
export default {
    name: 'reset',
    beforeCreate: function() {
        setPageTitle('Reset Password')
        const vm = this
        const token = vm.$route.query.token || ''
        get(`auth/reset?token=${token}`).then(function(data) {
            if (data.error) {
                vm.error = data.error
            } else if (data.success) {
                vm.token = token
                vm.form.email = data.success
            }
        });
    },
    data: function() {
        return {
            success: false,
            submitting: false,
            errors: {},
            error: null,
            token: null,
            successReset: false,
            form: {
                email: '',
                newPassword: '',
                newPasswordConfirm: ''
            }
        }
    },
    computed: {
        showForm: function() {
            return this.token && !this.successReset
        }
    },
    methods: {
        submit: function(e) {
            const vm = this
            reset(vm)
            post(`auth/reset?token=${vm.token}`, vm.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.successReset = true
                }
            });
        }
    }
}
</script>