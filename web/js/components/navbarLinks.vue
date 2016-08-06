
<template>
    <ul class="navbar-nav navbar-right nav">
        <router-link tag="li" to="/about" active-class="active"><a>About</a></router-link>
        <router-link tag="li" to="/contact" active-class="active"><a>Contact</a></router-link>
        <router-link tag="li" to="/account" active-class="active"><a>Account</a></router-link>
        <router-link tag="li" to="/profile" active-class="active"><a>Profile</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/login"><a>Login</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/login-email"><a>Login via Email</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/register"><a>Register</a></router-link>
        <li v-if="isLoggedIn" @click="logout"><a>Logout ({{ user.username }})</a></li>
    </ul>
</template>

<script>
import router from '../router.js'
export default {
    computed: Vuex.mapGetters([
        'user',
        'isGuest',
        'isLoggedIn'
    ]),
    methods: {
        logout (e) {
            this.$store.dispatch('logout')
            router.push('/')
        }
    }
}
</script>