
import {getConfig} from './functions.js'
import store from './store.js'
import router from './router.js'

// --------------------------------------------------------
// VM helpers (for use in .vue components)
// --------------------------------------------------------
export function reset (vm) {
    vm.submitting = true;
    vm.success = null;
    vm.error = null;
    vm.errors = {};
}

export function process (vm, data) {
    vm.submitting = false;
    if (data.success) {
        vm.success = data.success;
    }
    if (data.error) {
        vm.error = data.error;
    }
    if (data.errors) {
        vm.errors = data.errors;
    }
}

// --------------------------------------------------------
// Ajax shortcuts
// --------------------------------------------------------
export {get}
function get (url, data) {
    const params = $.extend(defaultConfig(), {
        url: getConfig('apiUrl') + url,
        method: 'GET',
        data: data
    })
    return $.ajax(params).then(successCallback, failureCallback);
}

export function post (url, data) {
    const params = $.extend(defaultConfig(), {
        url: getConfig('apiUrl') + url,
        method: 'POST',
        data: data
    })
    return $.ajax(params).then(successCallback, failureCallback);
}

// --------------------------------------------------------
// Ajax callback helper functions
// --------------------------------------------------------
function defaultConfig() {
    if (getConfig('jwtCookie')) {
        // needed for cross domain cookies
        return { xhrFields: { withCredentials: true } }
    } else if (store.getters.token) {
        return { headers: { Authorization: 'Bearer ' + store.getters.token } }
    }
}
function successCallback(data) {
    return data;
}

function failureCallback(data) {

    // store original ajax request and build reject object
    // @link http://stackoverflow.com/questions/21509278/jquery-deferred-reject-immediately
    const origAjax = this
    const reject = $.Deferred().reject()

    // check for non-401 -> alert the error and reject
    if (data.status != 401) {
        alert(`[ ${data.status} ] ${data.statusText}\n\n@ ${origAjax.url}`)
        return reject
    }

    // check for refresh token in local storage
    let refreshTokenData = null
    if (!getConfig('jwtCookie')) {
        refreshTokenData = store.getters.refreshToken ? { _refreshToken: store.getters.refreshToken } : null
        if (!refreshTokenData) {
            prepRedirect()
            return reject
        }
    }

    // attempt to refresh token, which was in local storage or maybe in cookies
    // if successful, send updated ajax request
    // otherwise, reject
    return get('auth/use-refresh-token', refreshTokenData).then(function(data) {
        if (data.success) {
            store.dispatch('login', data.success)
            const updatedAjax = $.extend(origAjax, defaultConfig())
            return $.ajax(updatedAjax)
        }

        // set login url and redirect to login page
        prepRedirect()
        return reject
    })
}

function prepRedirect() {
    store.dispatch('logout')
    store.commit('setLoginUrl', router.currentRoute.fullPath)
    router.push('/login')
}

// --------------------------------------------------------
// Global ajax - NProgress
// --------------------------------------------------------
$(document).ajaxStart(function() {
    NProgress.start()
});
$(document).ajaxStop(function() {
    NProgress.done()
});