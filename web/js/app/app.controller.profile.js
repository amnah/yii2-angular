(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl(User) {

        var vm = this;
        vm.User = User;
        vm.isLoaded = false;

        User.getUser().then(function(user) {
            if (!user) {
                User.authRedirect();
            }
            vm.isLoaded = true;
        });
    }

})();