
import store from './store.js'
import router from './router.js'
import {setConfig} from './functions.js'

import NavbarLinks from './components/navbarLinks.vue'

setConfig(AppConfig)

new Vue({
    el: '#app',
    store,
    router,
    components: { NavbarLinks }
})

