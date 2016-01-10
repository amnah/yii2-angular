(function () {
    'use strict';

    angular
        .module('app')
        .controller('AccountCtrl', AccountCtrl);

    // @ngInject
    function AccountCtrl(Api) {

        var vm = this;
        vm.submitting = false;
        vm.errors = {};

        var apiUrl = 'user/account';
        Api.get(apiUrl).then(function(data) {
            vm.User = data.success ? data.success.user : null;
            vm.UserToken = data.success ? data.success.userToken : null;
            vm.hasPassword = data.success ? data.success.hasPassword : false;
        });

        vm.submit = function() {
            vm.submitting = true;
            vm.errors = {};
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
            alert('resending');
        }
        vm.cancel = function() {
            alert('canceling');
        }
    }

})();