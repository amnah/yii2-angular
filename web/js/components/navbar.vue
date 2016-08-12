
<template>

    <div>
        <div class="navbar-header" @click="collapse">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <router-link class="navbar-brand" to="/">Yii 2 Vue</router-link>
        </div>

        <div id="navbar-collapse" class="collapse navbar-collapse">
            <ul class="navbar-nav navbar-right nav">
                <router-link tag="li" to="/about" active-class="active"><a @click="collapse">About</a></router-link>
                <router-link tag="li" to="/contact" active-class="active"><a @click="collapse">Contact</a></router-link>
                <router-link tag="li" to="/account" active-class="active"><a @click="collapse">Account</a></router-link>
                <router-link tag="li" to="/profile" active-class="active"><a @click="collapse">Profile</a></router-link>
                <router-link v-if="isGuest" tag="li" to="/register" active-class="active"><a @click="collapse">Register</a></router-link>
                <router-link v-if="isGuest" tag="li" to="/login" active-class="active" exact><a @click="collapse">Login</a></router-link>
                <router-link v-if="isGuest" tag="li" to="/login-email" active-class="active"><a @click="collapse">Login via Email</a></router-link>
                <li v-if="isLoggedIn"><a @click="logout">Logout ({{ user.email || user.username }})</a></li>
            </ul>
        </div>
    </div>

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
            const $navbar = $('#navbar-collapse');
            if ($navbar.hasClass('in')) {
                $navbar.collapse('hide')
            }
        }
    }
}
</script>