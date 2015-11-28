(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl($filter, $localStorage, Config, Api, Auth) {

        var vm = this;
        vm.user = null;
        vm.Auth = Auth;
        vm.message = '-----';
        var refreshToken = Auth.getRefreshToken();
        if (refreshToken) {
            vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Existing refresh token - ' + refreshToken.substr(-43);
        }
        Api.get('user').then(function(data) {
            vm.user = data.success;
        });

        vm.requestRefreshToken = function() {
            Auth.requestRefreshToken().then(function(data) {
                vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Got new refresh token - ' + data.success.substr(-43);
            });
        };

        vm.removeRefreshToken = function() {
            Auth.removeRefreshToken(true).then(function(data) {
                vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Removed refresh token';
            });
        };

        vm.useRefreshToken = function() {
            if (!Config.jwtCookie && !$localStorage.refreshToken) {
                vm.message = $filter('date')(new Date(), 'mediumTime') + ' - No refresh token (using local storage)';
            } else {
                Auth.useRefreshToken().then(function(data) {
                    vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Used refresh token to get new regular token';
                });
            }
        };
    }

})();