(function () {
    'use strict';

    angular
        .module('app')
        .controller('ConfirmCtrl', ConfirmCtrl);

    // @ngInject
    function ConfirmCtrl(Auth) {

        var vm = this;
        vm.success = false;
        vm.error = false;

        Auth.confirm().then(function(data) {
            if (data.success) {
                vm.success = data.success;
            } else if (data.error) {
                vm.error = data.error;
            }
        });
    }

})();