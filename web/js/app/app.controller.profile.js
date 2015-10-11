(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl(Api, Auth) {

        var vm = this;
        vm.user = null;
        vm.Auth = Auth;
        vm.message = '-----';

        Api.get('user').then(function(data) {
            vm.user = data.success;
        });

        vm.requestRefreshToken = function() {
            Auth.requestRefreshToken().then(function(data) {
                vm.message = 'Got new token - ' + data.success.substr(-43);
            });
        };

        vm.removeRefreshToken = function() {
            Auth.removeRefreshToken().then(function(data) {
                vm.message = 'Revoked token';
            });
        };

        vm.useRefreshToken = function() {
            if (Auth.getRefreshToken()) {
                Auth.useRefreshToken().then(function(data) {
                    vm.message = 'Used refresh token to get new regular token';
                });
            }
        };
    }

})();