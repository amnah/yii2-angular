(function () {
    'use strict';

    angular
        .module('app')
        .controller('AccountCtrl', AccountCtrl);

    // @ngInject
    function AccountCtrl(AjaxHelper, Api, Auth) {

        var vm = this;
        var apiUrl = 'user';
        Api.get(apiUrl).then(function(data) {
            vm.User = data.success ? data.success.user : null;
            vm.UserToken = data.success ? data.success.userToken : null;
            vm.hasPassword = data.success ? data.success.hasPassword : false;
        });

        vm.submit = function() {
            AjaxHelper.reset(vm);
            Api.post(apiUrl, vm.User).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    // refresh Auth user with latest data
                    Auth.getUser(true);
                    vm.successMsg = 'Account saved';
                    vm.User = data.success.user;
                    vm.UserToken = data.success.userToken;
                }
            });
        };

        vm.resend = function() {
            AjaxHelper.reset(vm);
            Api.post('user/change-resend').then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    vm.successMsg = 'Email resent';
                }
            });
        };
        vm.cancel = function() {
            AjaxHelper.reset(vm);
            Api.post('user/change-cancel').then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    vm.UserToken = null;
                    vm.successMsg = 'Email change cancelled'
                }
            });
        };
    }

})();