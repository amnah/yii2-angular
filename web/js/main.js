
import store from './store.js'
import router from './router.js'

import NavbarLinks from './navbarLinks.vue'

new Vue({
    el: '#app',
    store,
    router,
    components: { NavbarLinks }
})

