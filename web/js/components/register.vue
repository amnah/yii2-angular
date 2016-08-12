
<template>
    <div>
        <h1>Register</h1>

        <div class="success" v-if="success">
            <div class="alert alert-success">
                <p>User [ {{ form.email }} ] registered</p>
                <p v-if="userToken">Please check your email for an activation link</p>
                <p v-if="!userToken"><router-link to="/">Go home</router-link></p>
            </div>
        </div>

        <div v-if="!success">

            <p>Please fill out the following fields to register:</p>

            <form id="register-form" class="form-horizontal" role="form" @submit.prevent="submit">

                <div class="form-group" v-bind:class="{'has-error': errors.email}">
                    <label class="col-lg-1 control-label" for="register-form-email">Email</label>
                    <div class="col-lg-3">
                        <input type="text" id="register-form-email" class="form-control" v-model.trim="form.email">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
                    </div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': errors.newPassword}">
                    <label class="col-lg-1 control-label" for="register-form-newPassword">Password</label>
                    <div class="col-lg-3">
                        <input type="password" id="register-form-newPassword" class="form-control" v-model.trim="form.newPassword">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.newPassword">{{ errors.newPassword[0] }}</p>
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
import {post, reset, process} from '../api.js'
import router from '../router.js'
export default {
    name: 'register',
    beforeCreate: function() {
        setPageTitle('Register')
    },
    data: function() {
        return {
            success: false,
            submitting: false,
            errors: {},
            userToken: null,
            form: {
                email: '',
                newPassword: '',
                rememberMe: 1,
                jwtCookie: getConfig('jwtCookie')
            }
        }
    },
    methods: {
        submit (e) {
            const vm = this
            reset(vm)
            post('auth/register', vm.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.userToken = data.success.userToken
                    if (!vm.userToken) {
                        vm.$store.dispatch('login', data.success)
                    }
                }
            });
        }
    }
}
</script>