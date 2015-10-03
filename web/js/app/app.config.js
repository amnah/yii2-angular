(function () {
    'use strict';

    angular
        .module('app')
        .config(ajaxConfig);

    // @ngInject
    function ajaxConfig($locationProvider, $httpProvider) {

        // set up html5
        $locationProvider.html5Mode(true);

        // set up ajax requests
        // http://www.yiiframework.com/forum/index.php/topic/62721-yii2-and-angularjs-post/
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

        // add jwt into http headers
        // @ngInject
        $httpProvider.interceptors.push(function($localStorage) {
            return {
                request: function(config) {
                    if ($localStorage.jwt) {
                        config.headers.Authorization = 'Bearer ' + $localStorage.jwt;
                    }
                    return config;
                }
            };
        });
    }

})();