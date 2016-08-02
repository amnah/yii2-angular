
<template>
    <div>
        <h3>Contact</h3>
        <p>If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.</p>
    </div>
</template>

<script>
    import store from './store.js'
    export default {
        data () {
            return {
                //hello: 'world',
            }
        },
        computed: Vuex.mapGetters([
            'user',
            'isGuest',
            'isLoggedIn'
        ]),
        methods: {
            login: (event) => store.dispatch('login', {username:'bob2'}),
            logout: (event) => store.dispatch('logout')
        }
    }
</script>