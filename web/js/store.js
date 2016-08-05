

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
    login: ({ commit }, data) => {
        commit('setUserAndToken', data)
        localStorage.setItem('user', JSON.stringify(data.user))
        localStorage.setItem('token', JSON.stringify(data.token))
    },
    restoreLogin: ({ commit }) => {
        const data = {
            user: JSON.parse(localStorage.getItem('user')),
            token: JSON.parse(localStorage.getItem('token'))
        }
        if (data.user) {
            commit('setUserAndToken', data)
        }
    },
    logout: ({ commit }) => {
        commit('setUserAndToken', {user: null, token: null})
        localStorage.removeItem('user')
        localStorage.removeItem('token')
    }
}

// create the Vuex instance
export default new Vuex.Store({
    state,
    getters,
    mutations,
    actions,
})