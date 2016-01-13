(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginCallbackCtrl', LoginCallbackCtrl);

    // @ngInject
    function LoginCallbackCtrl(Auth) {

        var vm = this;
        vm.showRegister = false;
        vm.loginUrl = Auth.getLoginUrl();
        vm.User = {};

        Auth.loginCallback().then(function(data) {
            if (data.success && Auth.setUserAndToken(data)) {
                Auth.redirect(vm.loginUrl);
            } else if (data.error) {
                vm.error = data.error;
            } else if (data.email) {
                vm.User.email = data.email;
                vm.showRegister = true;
            }
        });

        // process form submit
        vm.submit = function() {

            // check captcha before making POST request
            vm.errors = {};
            vm.submitting  = true;
            Auth.loginCallback(vm.User).then(function(data) {
                vm.submitting  = false;
                if (data.success && Auth.setUserAndToken(data)) {
                    Auth.redirect(vm.loginUrl);
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };
    }

})();