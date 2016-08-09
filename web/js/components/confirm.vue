
<template>

    <div>

        <div class="alert alert-success" v-if="success">
            <p>Email [ {{ success }} ] confirmed</p>

            <p v-if="isLoggedIn"><router-link to="/account">Go to my account</router-link></p>
            <p v-if="isLoggedIn"><router-link to="/">Go home</router-link></p>
            <p v-if="!isLoggedIn"><router-link to="/login">Log in here</router-link></p>
        </div>

        <div class="alert alert-danger" v-if="error">
            {{ error }}
        </div>

    </div>

</template>

<script>
import {setPageTitle} from '../functions.js'
import {get, reset, process} from '../api.js'
export default {
    name: 'confirm',
    beforeCreate: function() {
        const vm = this
        setPageTitle('Confirm')
        reset(vm)
        get('auth/confirm', vm.$route.query).then(function(data) {
            process(vm, data)
            vm.$store.dispatch('renewLogin', true)
        });
    },
    data: function() {
        return {
            success: false,
            error: false,
            isLoggedIn: this.$store.getters.isLoggedIn,
        }
    }
}
</script>