(function () {
    'use strict';

    angular
        .module('app')
        .factory('AjaxHelper', AjaxHelper);

    // @ngInject
    function AjaxHelper() {

        var factory = {};

        factory.reset = function(vm) {
            vm.submitting = true;
            vm.success = null;
            vm.error = null;
            vm.errors = {};
        };

            factory.process = function(vm, data) {
            vm.submitting = false;
            if (data.success) {
                vm.success = data.success;
            }
            if (data.error) {
                vm.error = data.error;
            }
            if (data.errors) {
                vm.errors = data.errors;
            }
        };
        
        return factory;
    }

})();