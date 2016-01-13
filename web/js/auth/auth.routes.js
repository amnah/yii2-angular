(function () {
    'use strict';

    angular
        .module('app')
        .config(routeConfig);

    // @ngInject
    function routeConfig($routeProvider) {

        var viewDir = 'auth';
        var paths = ['register', 'confirm', 'login', 'login-email', 'login-callback', 'reset', 'account', 'profile'];
        for (var i=0; i<paths.length; i++) {
            var path = paths[i];
            $routeProvider.when('/' + path, {templateUrl: '/views/' + viewDir + '/' + path + '.html'});
        }
    }

})();