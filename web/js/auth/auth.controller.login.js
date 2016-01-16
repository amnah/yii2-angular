(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginCtrl', LoginCtrl);

    // @ngInject
    function LoginCtrl(Config, AjaxHelper, Auth) {

        var vm = this;
        vm.loginUrl = Auth.getLoginUrl();
        vm.LoginForm = { rememberMe: true, jwtCookie: Config.jwtCookie };

        // process form submit
        vm.submit = function() {
            AjaxHelper.reset(vm);
            Auth.login(vm.LoginForm).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success && Auth.setUserAndToken(data)) {
                    Auth.redirect(vm.loginUrl);
                }
            });
        };
    }

})();