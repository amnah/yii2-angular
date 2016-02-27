(function () {
    'use strict';

    angular
        .module('app')
        .config(routeConfig);

    // @ngInject
    function routeConfig($routeProvider, Config) {

        var viewDir = 'auth';
        var pathPrefix = Config.html5Mode ? '/' : '';
        var paths = ['register', 'confirm', 'login', 'login-email', 'login-callback', 'reset', 'account', 'profile'];
        for (var i=0; i<paths.length; i++) {
            var path = paths[i];
            $routeProvider.when('/' + path, {templateUrl: pathPrefix + 'views/' + viewDir + '/' + path + '.html'});
        }
    }

})();