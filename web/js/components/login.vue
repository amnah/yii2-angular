
<template>

    <div>
        <h1>Login</h1>

        <p>Please fill out the following fields to login:</p>

        <p v-if="loginUrl">After logging in, you will be redirected to <strong>{{ loginUrl }}</strong></p>

        <form id="login-form" class="form-horizontal" role="form" @submit.prevent="submit">

            <div class="form-group" v-bind:class="{'has-error': errors.email}">
                <label class="col-lg-1 control-label" for="login-form-email">Email</label>
                <div class="col-lg-3">
                    <input type="text" id="login-form-email" class="form-control" v-model.trim="form.email">
                </div>
                <div class="col-lg-8">
                    <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
                </div>
            </div>
            <div class="form-group" v-bind:class="{'has-error': errors.password}">
                <label class="col-lg-1 control-label" for="login-form-password">Password</label>
                <div class="col-lg-3">
                    <input type="password" id="login-form-password" class="form-control" v-model.trim="form.password">
                </div>
                <div class="col-lg-8">
                    <p class="help-block help-block-error" v-if="errors.password">{{ errors.password[0] }}</p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-offset-1 col-lg-3">
                    <input type="checkbox" id="login-form-rememberme" v-model="form.rememberMe" v-bind:true-value="1" v-bind:false-value="0">
                    <label for="login-form-rememberme">Remember Me</label>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-offset-1 col-lg-11">
                    <button type="submit" class="btn btn-primary" :disabled="submitting">Login</button>
                </div>
                <div class="col-lg-offset-1 col-lg-11">
                    <br/>
                    <p><router-link to="/register">Register</router-link></p>
                    <p><router-link to="/reset">Forgot password?</router-link></p>
                </div>
            </div>

        </form>

        <div class="col-lg-offset-1" style="color:#999;">
            You may login with <strong>neo/neo</strong>
        </div>
    </div>
</template>

<script>
import {setPageTitle, getConfig} from '../functions.js'
import {post, reset, process} from '../api.js'
import router from '../router.js'
export default {
    name: 'login',
    beforeCreate: function() {
        setPageTitle('Login')
    },
    data: function() {
        return {
            success: false,
            submitting: false,
            errors: {},
            loginUrl: this.$store.getters.loginUrl,
            form: {
                email: '',
                password: '',
                rememberMe: 1,
                jwtCookie: getConfig('jwtCookie')
            }
        }
    },
    methods: {
        submit (e) {
            const vm = this
            reset(vm)
            post('auth/login', vm.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.$store.dispatch('login', data.success)
                    vm.$store.commit('setLoginUrl', null)
                    router.push(vm.loginUrl || '/')
                }
            });
        }
    }
}
</script>