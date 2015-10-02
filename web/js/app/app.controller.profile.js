(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl(Api, User) {

        var vm = this;
        vm.User = User;

        Api.get('user').then(function(data) {
            vm.User.setUser(data.success);
        });
    }

})();