
<template>
    <div>
        <div class="jumbotron">
            <h2>Yii 2 Vue Boilerplate</h2>

            <p class="lead">Yii 2 REST server + Vue 2.0 client</p>

            <p><a class="btn btn-lg btn-success" href="https://github.com/amnah/yii2-vue">Check me out @ Github</a></p>
        </div>

        <div v-if="isLoggedIn" class="row">
            <div class="col-lg-5 col-lg-offset-2">

                <h3>Refresh tokens</h3>
                <p><strong>Status:</strong> {{ message }}</p>
                <p><strong>Refresh Token:</strong> {{ refreshToken.substr(-23) }}</p>
                <p><strong>Note:</strong> Refresh tokens are <strong>PERMANENT</strong> and typically used for mobile apps (NOT web apps)</p>

            </div>

            <div class="col-lg-2">
                <p><a class="btn btn-primary" @click="requestRefreshToken">Get refresh token</a></p>
                <p><a class="btn btn-danger" @click="removeRefreshToken">Remove refresh token</a></p>
                <p><a class="btn btn-success" @click="useRefreshToken">Use refresh token to get regular token</a></p>
            </div>
        </div>
    </div>
</template>

<script>
import {setPageTitle, getConfig} from '../functions.js'
import {get} from '../api.js'
export default {
    name: 'home',
    beforeCreate: function() {
        setPageTitle()
    },
    data: function() {
        const vm = this
        return {
            message: `${getMsgTime()} - Page loaded`,
            refreshToken: vm.$store.getters.refreshToken || ''
        }
    },
    computed: Vuex.mapGetters([
        'isLoggedIn'
    ]),
    methods: {
        requestRefreshToken (e) {
            const vm = this
            get('auth/request-refresh-token').then(function(data) {
                vm.message = `${getMsgTime()} - Got new refresh token`
                vm.refreshToken = data.success
                vm.$store.dispatch('storeRefreshToken', data.success)
            });
        },
        removeRefreshToken (e) {
            const vm = this
            get('auth/remove-refresh-token').then(function(data) {
                vm.message = `${getMsgTime()} - Removed refresh token`
                vm.refreshToken = ''
                vm.$store.dispatch('clearRefreshToken')
            })
        },
        useRefreshToken (e) {
            const vm = this
            if (!vm.refreshToken) {
                const storage = getConfig('jwtCookie') ? 'cookies' : 'local storage'
                vm.message = `${getMsgTime()} - No refresh token (using ${storage})`
            } else {
                get('auth/use-refresh-token', {_refreshToken: vm.refreshToken}).then(function(data) {
                    vm.message = `${getMsgTime()} - Used refresh token`
                })
            }
        }
    }
}

function getMsgTime() {
    return new Date().toLocaleTimeString()
}
</script>