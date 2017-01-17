
import store from './store.js'
import router from './router.js'
import {setConfig} from './functions.js'

setConfig(window.AppConfig)
delete window.AppConfig

store.dispatch('restoreFromStorage')
store.dispatch('checkAuth')

new Vue({
    el: '#app',
    store,
    router,
    components: {
        navbar: require('./components/navbar.vue')
    }
})
