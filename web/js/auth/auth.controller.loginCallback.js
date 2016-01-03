(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginCallbackCtrl', LoginCallbackCtrl);

    // @ngInject
    function LoginCallbackCtrl($routeParams, Config, Auth, Api) {

        var vm = this;
        vm.errors = {};
        vm.showInvalidToken = false;
        vm.showRegister = false;
        vm.loginUrl = Auth.getLoginUrl();
        vm.User = { rememberMe: true, jwtCookie: Config.jwtCookie };

        var token = $routeParams.token;
        var jwtCookie = Config.jwtCookie ? 1 : 0;
        var url = 'public/login-callback?token=' + token + '&jwtCookie=' + jwtCookie;
        Api.get(url).then(function(data) {
            if (data.success && Auth.setUserAndToken(data)) {
                Auth.redirect(vm.loginUrl).clearLoginUrl();
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
            Api.post(url, vm.User).then(function(data) {
                vm.submitting  = false;
                if (data.success && Auth.setUserAndToken(data)) {
                    Auth.redirect(vm.loginUrl).clearLoginUrl();
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };
    }

})();