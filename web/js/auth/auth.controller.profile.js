(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl($filter, $localStorage, Config, Api, Auth) {

        var vm = this;
        vm.Auth = Auth;
        vm.Profile = null;
        vm.message = '-----';
        vm.submitting = false;
        vm.errors = {};
        var refreshToken = Auth.getRefreshToken();
        if (refreshToken) {
            vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Existing refresh token - ' + refreshToken.substr(-43);
        }

        var apiUrl = 'user/profile';
        Api.get(apiUrl).then(function(data) {
            vm.Profile = data.success;
        });

        vm.submit = function() {
            vm.submitting = true;
            vm.errors = {};
            Api.post(apiUrl, vm.Profile).then(function(data) {
                vm.submitting = false;
                vm.Profile = data.success ? data.success : vm.Profile;
                vm.errors = data.errors ? data.errors : false;
            });
        };

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