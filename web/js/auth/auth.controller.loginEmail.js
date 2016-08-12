(function () {
    'use strict';

    angular
        .module('app')
        .controller('LoginEmailCtrl', LoginEmailCtrl);

    // @ngInject
    function LoginEmailCtrl(AjaxHelper, Auth) {

        var vm = this;
        vm.user = null;
        vm.LoginEmailForm = { rememberMe: true };

        // process form submit
        vm.submit = function() {
            AjaxHelper.reset(vm);
            Auth.loginEmail(vm.LoginEmailForm).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    vm.errors = false;
                    vm.user = data.success.user;
                }
            });
        };
    }

})();