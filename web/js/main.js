
import store from './store.js'
import router from './router.js'
import {setConfig} from './functions.js'

setConfig(AppConfig)
store.dispatch('restoreLogin')
if (store.getters.user) {
    store.dispatch('renewLogin')
}

new Vue({
    el: '#app',
    store,
    router,
    components: {
        navbarLinks: require('./components/navbarLinks.vue')
    }
})
