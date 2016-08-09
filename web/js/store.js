
import {get, post} from './api.js'
import {getConfig} from './functions.js'

// root state
const state = {
    user: null,
    token: null,
    refreshToken: null,
    loginUrl: null,
}

// getters
const getters = {
    user: state => state.user,
    isGuest: state => state.user ? false : true,
    isLoggedIn: state => state.user ? true : false,
    token: state => state.token,
    refreshToken: state => state.refreshToken,
    loginUrl: state => state.loginUrl
}

// mutations
const mutations = {
    setUser (state, newUser) {
        state.user = newUser
    },
    setToken (state, newToken) {
        state.token = newToken
    },
    setUserAndToken (state, data) {
        state.user = data.user
        state.token = data.token
    },
    setrefreshToken (state, newrefreshToken) {
        state.refreshToken = newrefreshToken
    },
    setLoginUrl (state, newLoginUrl) {
        state.loginUrl = newLoginUrl
    }
}

// actions
const actions = {
    login (state, data) {
        doLogin(state, data)
        startRenewLoginInterval(state)
    },
    logout (state) {
        doLogout(state)
        clearLoginInterval()
    },
    restoreLogin (state) {
        const data = {
            user: JSON.parse(localStorage.getItem('user')),
            token: JSON.parse(localStorage.getItem('token'))
        }
        if (data.user) {
            state.commit('setUserAndToken', data)
        }
    },
    startRenewLoginInterval,
    clearLoginInterval
}

function doLogin(state, data) {
    state.commit('setUserAndToken', data)
    localStorage.setItem('user', JSON.stringify(data.user))
    if (!getConfig('jwtCookie')) {
        localStorage.setItem('token', JSON.stringify(data.token))
    }
}

function doLogout(state) {
    post('auth/logout')
    state.commit('setUserAndToken', {user: null, token: null})
    localStorage.removeItem('user')
    localStorage.removeItem('token')
}

let jwtInterval = null
function startRenewLoginInterval(state, runAtStart) {
    clearLoginInterval()
    jwtInterval = setInterval(function() {
        renewLogin(state)
    }, getConfig('jwtIntervalTime'));
    if (runAtStart) {
        renewLogin(state)
    }
}

function renewLogin(state) {
    get('auth/renew-token').then(function(data) {
        if (data.success) {
            doLogin(state, data.success)
        } else {
            doLogout(state)
        }
    });
}

function clearLoginInterval() {
    clearInterval(jwtInterval);
}



// create the Vuex instance
export default new Vuex.Store({
    state,
    getters,
    mutations,
    actions,
})