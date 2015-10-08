(function () {
    'use strict';

    angular
        .module('app')
        .constant('Config', AppConfig)
        .config(ajaxConfig);

    // @ngInject
    function ajaxConfig($locationProvider, $httpProvider, Config) {

        // set up html5
        $locationProvider.html5Mode(true);

        // set up ajax requests
        // http://www.yiiframework.com/forum/index.php/topic/62721-yii2-and-angularjs-post/
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

        if (Config.useCookie) {
            $httpProvider.defaults.withCredentials = true;
        } else {
            // add token into http headers
            // @ngInject
            $httpProvider.interceptors.push(function($localStorage) {
                return {
                    request: function(config) {
                        if ($localStorage.token) {
                            config.headers.Authorization = 'Bearer ' + $localStorage.token;
                        }
                        return config;
                    }
                };
            });
        }
    }

})();