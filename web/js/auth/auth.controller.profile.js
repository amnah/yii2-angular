(function () {
    'use strict';

    angular
        .module('app')
        .controller('ProfileCtrl', ProfileCtrl);

    // @ngInject
    function ProfileCtrl(AjaxHelper, Api) {

        var vm = this;
        var apiUrl = 'user/profile';
        Api.get(apiUrl).then(function(data) {
            vm.Profile = data.success ? data.success.profile : null;
        });

        vm.submit = function() {
            AjaxHelper.reset(vm);
            Api.post(apiUrl, vm.Profile).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    vm.Profile = data.success.profile;
                }
            });
        };
    }

})();