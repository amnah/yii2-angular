
import {getConfig} from './functions.js'
import store from './store.js'
import router from './router.js'

// --------------------------------------------------------
// VM helpers (for use in .vue components)
// --------------------------------------------------------
export function reset(vm) {
    vm.submitting = true;
    vm.success = null;
    vm.error = null;
    vm.errors = {};
}

export function process(vm, data) {
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
function get(url, data) {
    const params = $.extend(defaultConfig(), {
        url: getConfig('apiUrl') + url,
        method: 'GET',
        data: data
    });
    return $.ajax(params).then(successCallback, failureCallback);
}

export function post(url, data) {
    const params = $.extend(defaultConfig(), {
        url: getConfig('apiUrl') + url,
        method: 'POST',
        data: data
    });
    return $.ajax(params).then(successCallback, failureCallback);
}

// --------------------------------------------------------
// Ajax callback helper functions
// --------------------------------------------------------
function defaultConfig() {
    // needed for cross domain cookies
    return { xhrFields: { withCredentials: true } }
}
function successCallback(data) {
    return data;
}

function failureCallback(data) {

    // check for 401 -> set url for redirection
    const origAjax = this;
    const reject = $.Deferred().reject();
    if (data.status == 401) {
        store.commit('setUser', null);
        store.commit('setLoginUrl', router.currentRoute.fullPath);
        router.push('/login');
        return reject;
    }

    // otherwise display the error message
    const msg = data.status ? `[ ${data.status} ] ${data.statusText}` : `[ Network error ] Please check your connection`;
    console.log(`${msg}\n\n@ ${origAjax.url}`);
    return reject;
}

// --------------------------------------------------------
// Global ajax - NProgress
// --------------------------------------------------------
let progressTimeout;
NProgress.configure({ trickleRate: 0.05, trickleSpeed: 200 });
$(document).ajaxStart(function() {
    clearTimeout(progressTimeout);
    progressTimeout = setTimeout(function() {
        NProgress.start()
    }, 500)

});
$(document).ajaxStop(function() {
    clearTimeout(progressTimeout);
    NProgress.done()
});