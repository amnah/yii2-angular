
import {getConfig} from './functions'

// --------------------------------------------------------
// Setup
// --------------------------------------------------------
$.ajaxSetup({
    xhrFields: { withCredentials: true } // needed for cross domain cookies
});

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
export function get (url, data) {
    return $.ajax({
        url: getConfig('apiUrl') + url,
        method: 'GET',
        data: data
    })
}

export function post (url, data) {
    return $.ajax({
        url: getConfig('apiUrl') + url,
        method: 'POST',
        data: data
    })
}
