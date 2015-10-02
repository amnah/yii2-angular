(function () {
    'use strict';

    angular
        .module('app')
        .config(routeConfig);

    // @ngInject
    function routeConfig($routeProvider) {

        var staticPaths = ['about', 'contact', 'register', 'login', 'profile'];
        for (var i=0; i<staticPaths.length; i++) {
            var path = staticPaths[i];
            $routeProvider.when('/' + path, {templateUrl: '/views/app/' + path + '.html'});
        }
        $routeProvider.otherwise({templateUrl: '/views/app/index.html'});
    }

})();