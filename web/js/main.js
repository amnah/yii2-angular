
import store from './store.js'
import router from './router.js'
import {setConfig} from './functions.js'

setConfig(AppConfig)
store.dispatch('restoreFromStorage')
if (store.getters.user) {
    store.dispatch('startRenewInterval', true)
}

new Vue({
    el: '#app',
    store,
    router,
    components: {
        navbar: require('./components/navbar.vue')
    }
})
