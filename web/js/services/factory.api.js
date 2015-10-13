(function () {
    'use strict';

    angular
        .module('app')
        .factory('Api', Api);

    // @ngInject
    function Api($http, $q, $location, $window, $localStorage, Config) {

        var factory = {};
        var apiUrl = Config.apiUrl;

        // define REST functions
        factory.get = function(url, data) {
            var config = getConfig();
            config = angular.extend(config, {params: data});
            return $http.get(apiUrl + url, config).then(processAjaxSuccess, processAjaxError);
        };
        factory.post = function(url, data) {
            var config = getConfig();
            return $http.post(apiUrl + url, data, config).then(processAjaxSuccess, processAjaxError);
        };
        factory.put = function(url, data) {
            var config = getConfig();
            return $http.put(apiUrl + url, data, config).then(processAjaxSuccess, processAjaxError);
        };
        // http://stackoverflow.com/questions/26479123/angularjs-http-delete-breaks-on-ie8
        factory['delete'] = function(url) {
            var config = getConfig();
            return $http['delete'](apiUrl + url, config).then(processAjaxSuccess, processAjaxError);
        };

        return factory;

        // get config
        // note: we need this as function so that it's called for every single Api call
        function getConfig() {
            var config = {};
            if (Config.useCookie) {
                config.withCredentials = true;
            } else if ($localStorage.token) {
                config.headers = {Authorization: 'Bearer ' + $localStorage.token};
            }
            return config;
        }

        // process ajax success/error calls
        function processAjaxSuccess(res) {
            return res.data;
        }
        function processAjaxError(res) {

            // process 401 by checking refresh token
            // otherwise just alert the error
            if (res.status == 401) {
                var params = $localStorage.refreshToken ? {refreshToken: $localStorage.refreshToken} : {};
                return $http.get(apiUrl + 'public/renew-token', {params: params}).then(processAjax401, processAjaxError);
            } else {
                var error = '[ ' + res.status + ' ] ' + (res.data.message || res.statusText);
                alert(error);
            }

            return $q.reject(res);
        }
        function processAjax401(res) {
            var data = res.data;
            if (data && data.success && data.success.user) {
                // set token if needed
                if (!Config.useCookie) {
                    $localStorage.token = data.success.token;
                }
            } else {
                $localStorage.loginUrl = $location.path();
                $location.path('/login').replace();
            }

            // reload so auth/user gets set properly
            $window.location.reload();
        }
    }

})();