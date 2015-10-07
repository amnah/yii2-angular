(function () {
    'use strict';

    angular
        .module('app')
        .factory('Api', Api);

    // @ngInject
    function Api($http, $q, $location, $localStorage, Config) {

        var factory = {};
        var apiUrl = Config.apiUrl;

        // define REST functions
        factory.get = function(url, data) {
            return $http.get(apiUrl + url, {params: data}).then(processAjaxSuccess, processAjaxError);
        };
        factory.post = function(url, data) {
            return $http.post(apiUrl + url, data).then(processAjaxSuccess, processAjaxError);
        };
        factory.put = function(url, data) {
            return $http.put(apiUrl + url, data).then(processAjaxSuccess, processAjaxError);
        };
        // http://stackoverflow.com/questions/26479123/angularjs-http-delete-breaks-on-ie8
        factory['delete'] = function(url) {
            return $http['delete'](apiUrl + url).then(processAjaxSuccess, processAjaxError);
        };

        return factory;

        // process ajax success/error calls
        function processAjaxSuccess(res) {
            return res.data;
        }
        function processAjaxError(res) {

            // process 401 by redirecting to login page
            // otherwise just alert the error
            if (res.status == 401) {
                $localStorage.loginUrl = $location.path();
                $location.path('/login').replace();
            } else {
                var error = '[ ' + res.status + ' ] ' + (res.data.message || res.statusText);
                alert(error);
            }

            // calculate and return error msg
            return $q.reject(res);
        }
    }

})();