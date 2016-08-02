
<template>
    <ul class="navbar-nav navbar-right nav">
        <li><a>About</a></li>
        <li><a>Contact</a></li>
        <li><a>Account</a></li>
        <li><a>Profile</a></li>
        <li v-if="isGuest" @click="login"><a v2-link="{ url: '/login' }">Login</a></li>
        <li v-if="isGuest"><a>Login via Email</a></li>
        <li v-if="isGuest"><a>Register</a></li>
        <li v-if="isLoggedIn" @click="logout"><a>Logout ({{ user.username }})</a></li>
    </ul>
</template>

<script>
import store from './store.js'
export default {
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