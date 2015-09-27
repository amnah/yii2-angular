(function () {
    'use strict';

    angular
        .module('app')
        .factory('User', User);

    // @ngInject
    function User($window, $location, $interval, $q, $localStorage, Api) {

        var factory = {};
        var user;

        // set minimum of once per minute just in case
        var minRefreshTime = 1000*60;
        var refreshInterval;
        var refreshTime = JWT_REFRESH_TIME; // some leeway is added on the server side
        if (refreshTime < minRefreshTime) {
            refreshTime = minRefreshTime;
        }

        factory.getAttributes = function() {
            return user ? user : null;
        };

        factory.getAttribute = function(attribute, defaultValue) {
            return user ? user[attribute] : defaultValue;
        };

        factory.isLoggedIn = function() {
            return user ? true : false;
        };

        // get login url via (1) local storage or (2) fallbackUrl
        factory.getLoginUrl = function(fallbackUrl) {
            var loginUrl = $localStorage.loginUrl;
            if (!loginUrl && fallbackUrl) {
                loginUrl = fallbackUrl;
            }
            if (!loginUrl) {
                loginUrl = '';
            }
            return loginUrl;
        };

        factory.redirect = function(url) {
            url = url ? url : '';
            $localStorage.loginUrl = '';
            $location.path(url).replace();
        };

        factory.startJwtRefreshInterval = function(runAtStart) {
            $interval.cancel(refreshInterval);
            refreshInterval = $interval(factory.doJwtRefresh, refreshTime);
            if (runAtStart) {
                factory.doJwtRefresh();
            }
        };

        factory.cancelJwtRefreshInterval = function() {
            $interval.cancel(refreshInterval);
        };

        factory.doJwtRefresh = function() {
            var jwtRefresh = $localStorage.jwtRefresh;
            if (jwtRefresh) {
                Api.post('public/jwt-refresh', {jwtRefresh: jwtRefresh}).then(function (data) {
                    factory.setUserAndJwt(data);
                });
            }
        };

        factory.loadFromLocalStorage = function() {
            user = $localStorage.user;
        };

        factory.setUser = function(userData) {
            user = userData;
        };

        factory.setUserAndJwt = function(data) {
            user = null;
            $localStorage.user = '';
            $localStorage.jwt = '';
            $localStorage.jwtRefresh = '';
            if (data && data.success && data.success.user) {
                user = data.success.user;
                $localStorage.user = data.success.user;
                $localStorage.jwt = data.success.jwt;
                $localStorage.jwtRefresh = data.success.jwtRefresh;
            }
        };

        factory.login = function(data) {
            return Api.post('public/login', data).then(function(data) {
                factory.setUserAndJwt(data);
                return data;
            });
        };

        factory.logout = function() {
            return Api.post('public/logout').then(function(data) {
                factory.setUserAndJwt(data);
                return data;
            });
        };

        factory.register = function(data) {
            return Api.post('public/register', data).then(function(data) {
                factory.setUserAndJwt(data);
                return data;
            });
        };

        // set up recaptcha
        var recaptchaDefer = $q.defer();
        $window.recaptchaLoaded = function() {
            recaptchaDefer.resolve($window.grecaptcha);
        };
        factory.getRecaptcha = function() {
            return recaptchaDefer.promise;
        };

        return factory;
    }

})();