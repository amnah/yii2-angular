
import {get, post} from './api.js'

// --------------------------------------------------------
// Root state
// --------------------------------------------------------
const state = {
    user: null,
    loginUrl: null,
}

// --------------------------------------------------------
// Getters
// --------------------------------------------------------
const getters = {
    user: state => state.user,
    isGuest: state => !state.user,
    isLoggedIn: state => !!state.user,
    loginUrl: state => state.loginUrl
}

// --------------------------------------------------------
// Mutations
// --------------------------------------------------------
const mutations = {
    setUser (state, user) {
        state.user = user
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
    },
    logout (state) {
        doLogout(state)
    },
    checkAuth,
    restoreFromStorage (state) {
        state.commit('setUser', JSON.parse(localStorage.getItem('user')))
    }
}

// --------------------------------------------------------
// Helper functions for actions
// --------------------------------------------------------
function doLogin(state, data) {
    state.commit('setUser', data.user)
    localStorage.setItem('user', JSON.stringify(data.user))
}

function doLogout(state) {
    post('auth/logout')
    state.commit('setUser', null)
    localStorage.removeItem('user')
}

function checkAuth(state) {
    if (!state.getters.user) {
        return
    }
    get('auth/check-auth').then(function(data) {
        if (data.success) {
            doLogin(state, data.success)
        } else {
            doLogout(state)
        }
    });
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