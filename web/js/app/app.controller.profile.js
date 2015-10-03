(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl(Api) {

        var vm = this;
        vm.user = null;
        
        Api.get('user').then(function(data) {
            vm.user = data.success;
        });
    }

})();