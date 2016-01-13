(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginCtrl', LoginCtrl);

    // @ngInject
    function LoginCtrl(Config, Auth) {

        var vm = this;
        vm.loginUrl = Auth.getLoginUrl();
        vm.LoginForm = { rememberMe: true, jwtCookie: Config.jwtCookie };

        // process form submit
        vm.submit = function() {
            vm.errors = {};
            vm.submitting  = true;
            Auth.login(vm.LoginForm).then(function(data) {
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