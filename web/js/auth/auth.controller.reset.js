(function () {
    'use strict';

    angular
        .module('app')
        .controller('ResetCtrl', ResetCtrl);

    // @ngInject
    function ResetCtrl($routeParams, Api) {

        var vm = this;
        vm.User = {};
        var forgotUrl = 'auth/forgot';
        var resetUrl = 'auth/reset';
        if (!$routeParams.token) {
            vm.showForgot = true;
        } else {
            resetUrl += '?token=' + $routeParams.token;
            Api.get(resetUrl).then(function(data) {
                if (data.success) {
                    vm.User.email = data.success;
                    vm.showReset = true;
                } else {
                    vm.error = 'Invalid token';
                }
            })
        }

        vm.submitForgot = function() {
            resetSubmit();
            Api.post(forgotUrl, vm.ForgotForm).then(function(data) {
                vm.submitting = false;
                if (data.success) {
                    vm.successForgot = true;
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };

        vm.submitReset = function() {
            resetSubmit();
            Api.post(resetUrl, vm.User).then(function(data) {
                vm.submitting = false;
                if (data.success) {
                    vm.successReset = true;
                } else if (data.errors) {
                    vm.errors = data.errors;
                } else if (data.error) {
                    vm.error = data.error;
                }
            });
        };

        function resetSubmit() {
            vm.submitting = true;
            vm.message = null;
            vm.success = null;
            vm.error = null;
            vm.errors = {};
        }
    }

})();