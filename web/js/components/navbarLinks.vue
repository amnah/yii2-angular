
<template>
    <ul class="navbar-nav navbar-right nav">
        <router-link tag="li" to="/about" active-class="active"><a  @click="collapse">About</a></router-link>
        <router-link tag="li" to="/contact" active-class="active"><a  @click="collapse">Contact</a></router-link>
        <router-link tag="li" to="/account" active-class="active"><a  @click="collapse">Account</a></router-link>
        <router-link tag="li" to="/profile" active-class="active"><a  @click="collapse">Profile</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/login" active-class="active"><a  @click="collapse">Login</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/login-email" active-class="active"><a  @click="collapse">Login via Email</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/register" active-class="active"><a  @click="collapse">Register</a></router-link>
        <li v-if="isLoggedIn"><a @click="logout">Logout ({{ user.email || user.username }})</a></li>
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
        },
        collapse (e) {
            $('#navbar-collapse').collapse('hide')
        }
    }
}
</script>