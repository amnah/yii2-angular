(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginCallbackCtrl', LoginCallbackCtrl);

    // @ngInject
    function LoginCallbackCtrl(AjaxHelper, Auth) {

        var vm = this;
        vm.showRegister = false;
        vm.loginUrl = Auth.getLoginUrl();
        vm.User = {};

        // handle login callback using $_GET token param
        Auth.loginCallback().then(function(data) {
            AjaxHelper.process(vm, data);
            if (data.success && Auth.setUserAndToken(data)) {
                Auth.redirect(vm.loginUrl);
            } else if (data.email) {
                vm.User.email = data.email;
                vm.showRegister = true;
            }
        });

        // process form submit
        vm.submit = function() {
            vm.errors = {};
            vm.submitting  = true;
            Auth.loginCallback(vm.User).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success && Auth.setUserAndToken(data)) {
                    Auth.redirect(vm.loginUrl);
                }
            });
        };
    }

})();