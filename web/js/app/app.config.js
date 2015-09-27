(function () {
    'use strict';

    angular
        .module('app')
        .config(ajaxConfig);

    // @ngInject
    function ajaxConfig($httpProvider) {

        // set up ajax requests
        // http://www.yiiframework.com/forum/index.php/topic/62721-yii2-and-angularjs-post/
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

        // add jwt into http headers
        $httpProvider.interceptors.push(['$localStorage', function($localStorage) {
            return {
                request: function(config) {
                    if ($localStorage.jwt) {
                        config.headers.Authorization = 'Bearer ' + $localStorage.jwt;
                    }
                    return config;
                }
            };
        }]);
    }

})();