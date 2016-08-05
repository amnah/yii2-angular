
import store from './store.js'
import router from './router.js'
import {setConfig,renewToken} from './functions.js'

import NavbarLinks from './components/navbarLinks.vue'

setConfig(AppConfig)
store.dispatch('restoreLogin')
if (store.getters.user) {
    store.dispatch('renewLogin')
}

new Vue({
    el: '#app',
    store,
    router,
    components: { NavbarLinks }
})

