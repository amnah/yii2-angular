(function () {
    'use strict';

    angular
        .module('app')
        .controller('NavCtrl', NavCtrl);

    // @ngInject
    function NavCtrl(Auth) {

        var vm = this;
        vm.isCollapsed = true;
        vm.Auth = Auth;
    }

})();