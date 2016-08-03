
<template>
    <div class="site-index">

        <div class="jumbotron">
            <h2>Yii 2 Vue Boilerplate</h2>

            <p class="lead">Yii 2 REST server + Vue 2.0 client</p>

            <p><a class="btn btn-lg btn-success" href="https://github.com/amnah/yii2-vue">Check me out @ Github</a></p>
        </div>

        <!--
        <div ng-if="vm.Auth.isLoggedIn()" class="row">

            <div class="col-lg-5 col-lg-offset-2">

                <h3>Refresh tokens</h3>
                <p><strong>Status:</strong> {{ vm.message }}</p>
                <p><strong>Refresh Token:</strong> {{ vm.refreshToken.substr(-23) }}</p>
                <p><strong>Note:</strong> Refresh tokens are <strong>PERMANENT</strong> and typically used for mobile apps -
                    NOT web apps.</p>

            </div>

            <div class="col-lg-2">
                <p><a class="btn btn-primary" ng-click="vm.requestRefreshToken()">Get refresh token</a></p>
                <p><a class="btn btn-danger" ng-click="vm.removeRefreshToken()">Remove refresh token</a></p>
                <p><a class="btn btn-success" ng-click="vm.useRefreshToken()">Use refresh token to get regular token</a></p>
            </div>

        </div>
        -->

    </div>
</template>

<script>
import {setPageTitle} from './functions.js'
export default {
    name: 'home',
    mounted: function() {
        setPageTitle()
    }
}
</script>