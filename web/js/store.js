
import {get, post} from './api.js'
import {getConfig} from './functions.js'

// --------------------------------------------------------
// Root state
// --------------------------------------------------------
const state = {
    user: null,
    token: null,
    refreshToken: null,
    loginUrl: null,
}

// --------------------------------------------------------
// Getters
// --------------------------------------------------------
const getters = {
    user: state => state.user,
    isGuest: state => state.user ? false : true,
    isLoggedIn: state => state.user ? true : false,
    token: state => state.token,
    refreshToken: state => state.refreshToken,
    loginUrl: state => state.loginUrl
}

// --------------------------------------------------------
// Mutations
// --------------------------------------------------------
const mutations = {
    setUserAndToken (state, data) {
        state.user = data.user
        state.token = data.token
    },
    setRefreshToken (state, refreshToken) {
        state.refreshToken = refreshToken
    },
    setLoginUrl (state, loginUrl) {
        state.loginUrl = loginUrl
    }
}

// --------------------------------------------------------
// Actions
// --------------------------------------------------------
const actions = {
    login (state, data) {
        doLogin(state, data)
        startRenewInterval(state)
    },
    logout (state) {
        doLogout(state)
        clearRenewInterval()
    },
    restoreFromStorage (state) {
        const data = {
            user: JSON.parse(localStorage.getItem('user')),
            token: JSON.parse(localStorage.getItem('token')),
            refreshToken: localStorage.getItem('refreshToken')
        }
        if (data.user) {
            state.commit('setUserAndToken', data)
        }
        if (data.refreshToken) {
            state.commit('setRefreshToken', data.refreshToken)
        }
    },
    renewLogin,
    startRenewInterval,
    clearRenewInterval,
    storeRefreshToken (state, refreshToken) {
        state.commit('setRefreshToken', refreshToken)
        if (!getConfig('jwtCookie')) {
            localStorage.setItem('refreshToken', refreshToken)
        }
    },
    clearRefreshToken (state) {
        state.commit('setRefreshToken', null)
        localStorage.removeItem('refreshToken')
    }
}

// --------------------------------------------------------
// Helper functions for actions
// --------------------------------------------------------
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
    localStorage.removeItem('refreshToken')
}

let jwtInterval = null
function startRenewInterval(state, runAtStart) {
    clearRenewInterval()
    jwtInterval = setInterval(function() {
        renewLogin(state)
    }, getConfig('jwtIntervalTime'));
    if (runAtStart) {
        renewLogin(state)
    }
}

function renewLogin(state, refresh) {
    const data = refresh ? { refreshDb: 1 } : null
    get('auth/renew-token', data).then(function(data) {
        if (data.success) {
            doLogin(state, data.success)
        } else {
            doLogout(state)
        }
    });
}

function clearRenewInterval() {
    clearInterval(jwtInterval);
}


// --------------------------------------------------------
// Vuex instance
// --------------------------------------------------------
export default new Vuex.Store({
    state,
    getters,
    mutations,
    actions,
})