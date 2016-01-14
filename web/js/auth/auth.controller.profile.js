(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl(Api) {

        var vm = this;
        var apiUrl = 'user/profile';
        Api.get(apiUrl).then(function(data) {
            vm.Profile = data.success ? data.success.profile : null;
        });

        vm.submit = function() {
            vm.submitting = true;
            vm.errors = {};
            Api.post(apiUrl, vm.Profile).then(function(data) {
                vm.submitting = false;
                vm.Profile = data.success ? data.success.profile : vm.Profile;
                vm.errors = data.errors ? data.errors : false;
            });
        };
    }

})();