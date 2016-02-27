(function () {
    'use strict';

    angular
        .module('app')
        .controller('ResetCtrl', ResetCtrl);

    // @ngInject
    function ResetCtrl($routeParams, AjaxHelper, Api) {

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
                AjaxHelper.process(vm, data);
                if (data.success) {
                    vm.successForgot = true;
                }
            });
        };

        vm.submitReset = function() {
            resetSubmit();
            Api.post(resetUrl, vm.User).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    vm.successReset = true;
                }
            });
        };

        function resetSubmit() {
            AjaxHelper.reset(vm);
            vm.successForgot = null;
            vm.successReset = null;
        }
    }

})();