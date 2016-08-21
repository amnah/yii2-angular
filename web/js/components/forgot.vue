
<template>
    <div>
        <div class="alert alert-success" v-if="success">
            <p>Please check your email for a reset password link</p>
        </div>

        <form id="forgot-form" class="form-horizontal" role="form" v-if="!success" @submit.prevent="submit">

            <h1>Forgot password</h1>
            <p>We'll send you a link to reset your password</p>
            <div class="form-group" v-bind:class="{'has-error': errors.email}">
                <label class="col-lg-1 control-label" for="forgot-form-email">Email</label>
                <div class="col-lg-4">
                    <input type="text" id="forgot-form-email" class="form-control" v-model.trim="form.email">
                </div>
                <div class="col-lg-7">
                    <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
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
import {post, reset, process} from '../api.js'
export default {
    name: 'forgot',
    beforeCreate: function() {
        setPageTitle('Forgot Password')
    },
    data: function() {
        return {
            success: false,
            submitting: false,
            errors: {},
            form: {
                email: ''
            }
        }
    },
    methods: {
        submit: function(e) {
            const vm = this
            reset(vm)
            post('auth/forgot', vm.form).then(function(data) {
                process(vm, data)
            });
        }
    }
}
</script>