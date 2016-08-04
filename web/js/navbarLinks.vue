
<template>
    <ul class="navbar-nav navbar-right nav">
        <router-link tag="li" to="/about" active-class="active"><a>About</a></router-link>
        <router-link tag="li" to="/contact" active-class="active"><a>Contact</a></router-link>
        <router-link tag="li" to="/account" active-class="active"><a>Account</a></router-link>
        <router-link tag="li" to="/profile" active-class="active"><a>Profile</a></router-link>
        <li v-if="isGuest" @click="login"><a>Login</a></li>
        <router-link v-if="isGuest" tag="li" to="/login-email"><a>Login via Email</a></router-link>
        <router-link v-if="isGuest" tag="li" to="/register"><a>Register</a></router-link>
        <li v-if="isLoggedIn" @click="logout"><a>Logout ({{ user.username }})</a></li>
    </ul>
</template>

<script>
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
        login (e) {
            this.$store.dispatch('login', {username:'bob2', email:'bob2@bob.com'})
        },
        logout (e) {
            this.$store.dispatch('logout')
        }
    }
}
</script>