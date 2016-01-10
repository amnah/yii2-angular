(function () {
    'use strict';

    angular
        .module('app')
        .controller('AccountCtrl', AccountCtrl);

    // @ngInject
    function AccountCtrl(Api) {

        var vm = this;
        vm.submitting = false;
        vm.message = null;
        vm.errors = {};

        var apiUrl = 'user';
        Api.get(apiUrl).then(function(data) {
            vm.User = data.success ? data.success.user : null;
            vm.UserToken = data.success ? data.success.userToken : null;
            vm.hasPassword = data.success ? data.success.hasPassword : false;
        });

        vm.submit = function() {
            resetSubmit();
            Api.post(apiUrl, vm.User).then(function(data) {
                var currentPassword = vm.User.currentPassword;
                vm.submitting = false;
                vm.User = data.success ? data.success.user : vm.User;
                vm.User.currentPassword = currentPassword;
                vm.UserToken = data.success ? data.success.userToken : vm.UserToken;
                vm.errors = data.errors ? data.errors : false;
            });
        };

        vm.resend = function() {
            resetSubmit();
            Api.post('user/change-resend').then(function(data) {
                vm.submitting = false;
                if (data.success) {
                    vm.message = 'Email resent';
                }
            });
        };
        vm.cancel = function() {
            resetSubmit();
            Api.post('user/change-cancel').then(function(data) {
                vm.submitting = false;
                if (data.success) {
                    vm.UserToken = null;
                    vm.message = 'Email change cancelled'
                }
            });
        };

        function resetSubmit() {
            vm.submitting = true;
            vm.message = null;
            vm.errors = {};
        }
    }

})();