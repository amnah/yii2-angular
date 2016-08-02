
Vue.use(VueRouter)

export default new VueRouter({
    mode: 'history',
    scrollBehavior: require('./functions.js').scrollBehavior,
    routes: [
        { path: '/', component: require('./home.vue') },
        { path: '/about', component: require('./about.vue') },
        { path: '/contact', component: require('./contact.vue') },
    ]
})
