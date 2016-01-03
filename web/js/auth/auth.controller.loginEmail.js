(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginEmailCtrl', LoginEmailCtrl);

    // @ngInject
    function LoginEmailCtrl($routeParams, Config, Api) {

        var vm = this;
        vm.errors = {};
        vm.user = null;
        vm.LoginEmailForm = { rememberMe: true };

        // process form submit
        vm.submit = function() {
            vm.errors = {};
            vm.submitting  = true;
            Api.post('public/login-email', vm.LoginEmailForm).then(function(data) {
                vm.submitting  = false;
                if (data.success) {
                    vm.errors = false;
                    vm.user = data.user;
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };
    }

})();