(function () {
    'use strict';

    angular
        .module('app')
        .controller('NavCtrl', NavCtrl);

    // @ngInject
    function NavCtrl($scope, $localStorage, $location, Api, Auth, authService) {

        var vm = this;
        vm.isCollapsed = true;
        vm.Auth = Auth;

        $scope.$on('event:auth-loginRequired', function() {

            // use refresh token
            // note: the Auth.useRefreshToken() call already handles updating user/token in the factory
            Auth.useRefreshToken().then(function(data) {
               if (Auth.isLoggedIn()) {
                   authService.loginConfirmed(data, function(config) {
                       return angular.extend(config, Api.getConfig());
                   });
               } else {
                   Auth.setLoginUrl($location.path()).redirect('/login');
               }
            });
        });
    }

})();