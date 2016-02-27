(function () {
    'use strict';

    angular
        .module('app')
        .controller('IndexCtrl', IndexCtrl);

    // @ngInject
    function IndexCtrl($filter, $localStorage, Config, Auth) {

        var vm = this;
        vm.Auth = Auth;

        // check for existing refresh token (local storage only. it won't work for cookies!)
        vm.refreshToken = Auth.getRefreshToken();
        if (vm.refreshToken) {
            vm.message = getMsgDate() + ' - Has refresh token';
        } else if (!vm.refreshToken && !Config.jwtCookie) {
            vm.message = getMsgDate() + ' - No refresh token (using local storage)';
        }

        vm.requestRefreshToken = function() {
            Auth.requestRefreshToken().then(function(data) {
                vm.refreshToken = data.success;
                vm.message = getMsgDate() + ' - Got new refresh token';
            });
        };

        vm.removeRefreshToken = function() {
            Auth.removeRefreshToken(true).then(function(data) {
                vm.refreshToken = null;
                vm.message = getMsgDate() + ' - Removed refresh token';
            });
        };

        vm.useRefreshToken = function() {
            if (!vm.refreshToken && !Config.jwtCookie) {
                vm.message = getMsgDate() + ' - No refresh token (using local storage)';
            } else if (!vm.refreshToken) {
                vm.message = getMsgDate() + ' - No refresh token (using cookies)';
            } else {
                Auth.useRefreshToken().then(function(data) {
                    vm.message = getMsgDate() + ' - Used refresh token ';
                });
            }
        };

        function getMsgDate() {
            return $filter('date')(new Date(), 'mediumTime');
        }
    }

})();