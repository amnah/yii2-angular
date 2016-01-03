(function () {
    'use strict';

    angular
        .module('app')
        .config(routeConfig);

    // @ngInject
    function routeConfig($routeProvider) {

        var viewDir = 'app';
        var paths = ['about', 'contact'];
        for (var i=0; i<paths.length; i++) {
            var path = paths[i];
            $routeProvider.when('/' + path, {templateUrl: '/views/' + viewDir + '/' + path + '.html'});
        }

        // set home and 404 pages
        $routeProvider.when('/', {templateUrl: '/views/app/index.html'});
        $routeProvider.otherwise({templateUrl: '/views/app/404.html'});
    }

})();