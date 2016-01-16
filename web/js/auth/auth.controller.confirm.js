(function () {
    'use strict';

    angular
        .module('app')
        .controller('ConfirmCtrl', ConfirmCtrl);

    // @ngInject
    function ConfirmCtrl(AjaxHelper, Auth) {

        var vm = this;
        vm.Auth = Auth;

        Auth.confirm().then(function(data) {
            AjaxHelper.process(vm, data);
        });
    }

})();