
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
                <router-link tag="li" to="/about" active-class="active" @click.native="collapse"><a>About</a></router-link>
                <router-link tag="li" to="/contact" active-class="active" @click.native="collapse"><a>Contact</a></router-link>
                <router-link tag="li" to="/account" active-class="active" @click.native="collapse"><a>Account</a></router-link>
                <router-link tag="li" to="/profile" active-class="active" @click.native="collapse"><a>Profile</a></router-link>
                <router-link v-if="isGuest" tag="li" to="/register" active-class="active" @click.native="collapse"><a>Register</a></router-link>
                <router-link v-if="isGuest" tag="li" to="/login" active-class="active" exact @click.native="collapse"><a>Login</a></router-link>
                <router-link v-if="isGuest" tag="li" to="/login-email" active-class="active" @click.native="collapse"><a>Login via Email</a></router-link>
                <li v-if="isLoggedIn"><a @click="logout">Logout ({{ user.email || user.username }})</a></li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    computed: Vuex.mapGetters([
        'user',
        'isGuest',
        'isLoggedIn'
    ]),
    methods: {
        logout: function(e) {
            this.$store.dispatch('logout')
            this.$router.push('/')
        },
        collapse: function(e) {
            const $navbar = $('#navbar-collapse');
            if ($navbar.hasClass('in')) {
                $navbar.collapse('hide')
            }
        }
    }
}
</script>