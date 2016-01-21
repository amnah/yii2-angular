(function () {
    'use strict';

    angular
        .module('app')
        .config(routeConfig);

    // @ngInject
    function routeConfig($routeProvider, Config) {

        var viewDir = 'app';
        var pathPrefix = Config.html5Mode ? '/' : '';
        var paths = ['about', 'contact'];
        for (var i=0; i<paths.length; i++) {
            var path = paths[i];
            $routeProvider.when('/' + path, {templateUrl: pathPrefix + 'views/' + viewDir + '/' + path + '.html'});
        }

        // set home and 404 pages
        $routeProvider.when('/', {templateUrl: pathPrefix + 'views/app/index.html'});
        $routeProvider.otherwise({templateUrl: pathPrefix + 'views/app/404.html'});
    }

})();