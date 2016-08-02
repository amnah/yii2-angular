

// root state
const state = {
    user: null
}

// getters
const getters = {
    user: state => state.user,
    isGuest: state => state.user ? false : true,
    isLoggedIn: state => state.user ? true : false
}

// actions
const actions = {
    login: ({ commit }, newUser) => commit('updateUser', newUser),
    logout: ({ commit }) => commit('updateUser', null),
}

// mutations
const mutations = {
    updateUser (state, newUser) {
        state.user = newUser
    }
}

// create the Vuex instance
export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations
})