(function () {
    'use strict';

    angular
        .module('app')
        .controller('AccountCtrl', AccountCtrl);

    // @ngInject
    function AccountCtrl(Api) {

        var vm = this;
        var apiUrl = 'user';
        Api.get(apiUrl).then(function(data) {
            vm.User = data.success ? data.success.user : null;
            vm.UserToken = data.success ? data.success.userToken : null;
            vm.hasPassword = data.success ? data.success.hasPassword : false;
        });

        vm.submit = function() {
            resetSubmit();
            Api.post(apiUrl, vm.User).then(function(data) {
                vm.submitting = false;
                vm.User = data.success ? data.success.user : vm.User;
                vm.UserToken = data.success ? data.success.userToken : vm.UserToken;
                vm.errors = data.errors ? data.errors : false;
            });
        };

        vm.resend = function() {
            resetSubmit();
            Api.post('user/change-resend').then(function(data) {
                vm.submitting = false;
                if (data.success) {
                    vm.success = 'Email resent';
                }
            });
        };
        vm.cancel = function() {
            resetSubmit();
            Api.post('user/change-cancel').then(function(data) {
                vm.submitting = false;
                if (data.success) {
                    vm.UserToken = null;
                    vm.success = 'Email change cancelled'
                }
            });
        };

        function resetSubmit() {
            vm.submitting = true;
            vm.success = null;
            vm.errors = {};
        }
    }

})();