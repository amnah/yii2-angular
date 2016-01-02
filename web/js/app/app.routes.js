(function () {
    'use strict';

    angular
        .module('app')
        .config(routeConfig);

    // @ngInject
    function routeConfig($routeProvider) {

        $routeProvider.when('/', {templateUrl: '/views/app/index.html'});
        $routeProvider.otherwise({templateUrl: '/views/app/404.html'});

        // handle static root paths
        var staticPaths = ['about', 'contact', 'register', 'login', 'login-email', 'login-callback', 'profile', 'confirm'];
        for (var i=0; i<staticPaths.length; i++) {
            var path = staticPaths[i];
            $routeProvider.when('/' + path, {templateUrl: '/views/app/' + path + '.html'});
        }
    }

})();