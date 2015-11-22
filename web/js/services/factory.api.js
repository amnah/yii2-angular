(function () {
    'use strict';

    angular
        .module('app')
        .factory('Api', Api);

    // @ngInject
    function Api($http, $q, $localStorage, Config) {

        var factory = {};
        var apiUrl = Config.apiUrl;

        // define REST functions
        factory.get = function(url, data) {
            var config = factory.getConfig();
            config = angular.extend(config, {params: data});
            return $http.get(apiUrl + url, config).then(processAjaxSuccess, processAjaxError);
        };
        factory.post = function(url, data) {
            var config = factory.getConfig();
            return $http.post(apiUrl + url, data, config).then(processAjaxSuccess, processAjaxError);
        };
        factory.put = function(url, data) {
            var config = factory.getConfig();
            return $http.put(apiUrl + url, data, config).then(processAjaxSuccess, processAjaxError);
        };
        // http://stackoverflow.com/questions/26479123/angularjs-http-delete-breaks-on-ie8
        factory['delete'] = function(url) {
            var config = factory.getConfig();
            return $http['delete'](apiUrl + url, config).then(processAjaxSuccess, processAjaxError);
        };

        // get config
        // note: we need this as function so that it's called for every single Api call
        factory.getConfig = function() {
            var config = {};
            if (Config.jwtCookie) {
                config.withCredentials = true;
            } else if ($localStorage.token) {
                config.headers = config.headers || {};
                config.headers.Authorization = 'Bearer ' + $localStorage.token;
            }
            return config;
        };
        
        return factory;

        // process ajax success/error calls
        function processAjaxSuccess(res) {
            return res.data;
        }
        function processAjaxError(res) {
            var error = '[ ' + res.status + ' ] ' + (res.data.message || res.statusText);
            alert(error);
            return $q.reject(res);
        }
    }

})();