(function () {
    'use strict';

    angular
        .module('app')
        .controller('NavCtrl', NavCtrl);

    // @ngInject
    function NavCtrl(User) {

        var vm = this;
        vm.User = User;
        vm.isCollapsed = true;

        vm.logout = function() {
            User.logout().then(function(data) {
                User.redirect('/');
            });
        };
    }

})();