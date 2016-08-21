
<template>
    <div>
        <div class="alert alert-success" v-if="successLogin">
            <p>Login successful for [ {{ successLogin }} ]</p>
            <p><router-link to="/">Go home</router-link></p>
        </div>
        <div class="alert alert-success" v-if="successRegister">
            <p>Registration successful for [ {{ successRegister }} ]</p>
            <p><router-link to="/">Go home</router-link></p>
        </div>
        <div class="alert alert-danger" v-if="error">{{ error }}</div>

        <div v-if="showForm">
            <h1>Register</h1>

            <p>Please fill out the following fields to register:</p>

            <form id="login-callback-form" class="form-horizontal" role="form" @submit.prevent="submit">

                <div class="form-group" v-bind:class="{'has-error': errors.email}">
                    <label class="col-lg-1 control-label" for="login-callback-form-email">Email</label>
                    <div class="col-lg-3">
                        <input type="text" id="login-callback-form-email" class="form-control" disabled="disabled" v-model.trim="form.email">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
                    </div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': errors.username}">
                    <label class="col-lg-1 control-label" for="login-callback-form-username">Username</label>
                    <div class="col-lg-3">
                        <input type="text" id="login-callback-form-username" class="form-control" v-model.trim="form.username">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.username">{{ errors.username[0] }}</p>
                    </div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': errors.full_name}">
                    <label class="col-lg-1 control-label" for="login-callback-form-full_name">Full name</label>
                    <div class="col-lg-3">
                        <input type="text" id="login-callback-form-full_name" class="form-control" v-model.trim="form.full_name">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.full_name">{{ errors.full_name[0] }}</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <button type="submit" class="btn btn-primary" :disabled="submitting">Register</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
import {setPageTitle, getConfig} from '../functions.js'
import {get, post, reset, process} from '../api.js'
export default {
    name: 'loginCallback',
    beforeCreate: function() {
        setPageTitle('Register')
        const vm = this
        const token = vm.$route.query.token || ''
        const jwtCookie = getConfig('jwtCookie') ? 1 : 0
        get(`auth/login-callback?token=${token}&jwtCookie=${jwtCookie}`).then(function(data) {
            if (data.error) {
                vm.error = data.error
            } else if (data.success && data.success.user) {
                vm.$store.dispatch('login', data.success)
                vm.successLogin = data.success.user.email
            } else if (data.success && data.email) {
                vm.token = token
                vm.jwtCookie = jwtCookie
                vm.form.email = data.email
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
            jwtCookie : 0,
            successLogin: false,
            successRegister: false,
            form: {
                email: '',
                username: '',
                full_name: ''
            }
        }
    },
    computed: {
        showForm: function() {
            return this.token && !this.successLogin && !this.successRegister
        }
    },
    methods: {
        submit: function(e) {
            const vm = this
            reset(vm)
            post(`auth/login-callback?token=${vm.token}&jwtCookie=${vm.jwtCookie}`, vm.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.$store.dispatch('login', data.success)
                    vm.successRegister = vm.form.email
                }
            });
        }
    }
}
</script>