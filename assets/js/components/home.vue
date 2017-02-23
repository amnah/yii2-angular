
<template>
    <div>
        <div class="jumbotron">
            <h2>Yii 2 Vue Boilerplate</h2>

            <p class="lead">Yii 2 REST server + Vue 2.0 client</p>

            <p><a class="btn btn-lg btn-success" href="https://github.com/amnah/yii2-vue">Check me out @ Github</a></p>
        </div>
    </div>
</template>

<script>
import {setPageTitle} from '../functions.js'
import {get} from '../api.js'
export default {
    name: 'home',
    beforeCreate: function() {
        setPageTitle()
    }
}
</script>