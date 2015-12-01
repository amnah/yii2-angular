(function () {
    'use strict';

    angular
        .module('app')
        .controller('ConfirmCtrl', ConfirmCtrl);

    // @ngInject
    function ConfirmCtrl($routeParams, Api, Auth) {

        var vm = this;
        vm.success = false;
        vm.error = false;

        var token = $routeParams.token;
        Api.get('public/confirm', {token: token}).then(function(data) {
            if (data.success) {
                vm.success = data.success;
            } else if (data.error) {
                vm.error = data.error;
            }
        });
    }

})();