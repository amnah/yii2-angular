(function () {
    'use strict';

    angular
        .module('app')
        .controller('NavCtrl', NavCtrl);

    // @ngInject
    function NavCtrl(User) {

        var vm = this;
        vm.isCollapsed = true;
        vm.User = User;
    }

})();