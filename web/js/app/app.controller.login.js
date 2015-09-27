(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginCtrl', LoginCtrl);

    // @ngInject
    function LoginCtrl(User) {

        var vm = this;
        vm.errors = {};
        vm.loginUrl = User.getLoginUrl();
        vm.LoginForm = { rememberMe: true };

        // process form submit
        vm.submit = function() {
            vm.errors = {};
            vm.submitting  = true;
            User.login(vm.LoginForm).then(function(data) {
                vm.submitting  = false;
                if (data.success) {
                    User.startJwtRefreshInterval();
                    User.redirect(vm.loginUrl);
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };
    }

})();