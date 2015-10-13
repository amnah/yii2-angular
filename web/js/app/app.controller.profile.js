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

        Api.get('user').then(function(data) {
            vm.user = data.success;
        });

        vm.requestRefreshToken = function() {
            Auth.requestRefreshToken().then(function(data) {
                vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Got new token - ' + data.success.substr(-43);
            });
        };

        vm.removeRefreshToken = function() {
            Auth.removeRefreshToken().then(function(data) {
                vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Removed token';
            });
        };

        vm.useRefreshToken = function() {
            if (!Config.useCookie && !$localStorage.refreshToken) {
                vm.message = $filter('date')(new Date(), 'mediumTime') + ' - No refresh token (using local storage)';
            } else {
                Auth.useRefreshToken().then(function(data) {
                    vm.message = $filter('date')(new Date(), 'mediumTime') + ' - Used refresh token to get new regular token';
                });
            }
        };
    }

})();