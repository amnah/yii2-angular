
<template>
    <div>
        <h1>Login</h1>

        <div class="alert alert-success" v-if="success">
            <p v-if="user">Login link sent - Please check your email</p>
            <p v-if="!user">Registration link sent - Please check your email</p>
        </div>

        <div v-if="!success">

            <p>This will send a link to the email address to log in or register</p>

            <p>These links expire in 15 minutes</p>

            <form id="login-email-form" class="form-horizontal" role="form" @submit.prevent="submit">

                <div class="form-group" v-bind:class="{'has-error': errors.email}">
                    <label class="col-lg-1 control-label" for="login-email-form-email">Email</label>
                    <div class="col-lg-3">
                        <input type="text" id="login-email-form-email" class="form-control" v-model.trim="form.email">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
                    </div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': errors.rememberMe}">
                    <div class="col-lg-offset-1 col-lg-3">
                        <input type="checkbox" id="login-email-form-rememberme" v-model="form.rememberMe" v-bind:true-value="1" v-bind:false-value="0">
                        <label for="login-email-form-rememberme">Remember Me</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <button type="submit" class="btn btn-primary" :disabled="submitting">Login</button>
                    </div>
                </div>

            </form>

        </div>

    </div>
</template>

<script>
import {setPageTitle} from '../functions.js'
import {post, reset, process} from '../api.js'
export default {
    name: 'loginEmail',
    beforeCreate: function() {
        setPageTitle('Login')
    },
    data: function() {
        return {
            success: false,
            submitting: false,
            errors: {},
            user: null,
            form: {
                email: '',
                rememberMe: 1
            }
        }
    },
    methods: {
        submit (e) {
            const vm = this
            reset(vm)
            post('auth/login-email', vm.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    vm.user = data.success.user;
                }
            });
        }
    }
}
</script>