(function () {
    'use strict';

    angular
        .module('app')
        .factory('User', User);

    // @ngInject
    function User($window, $location, $interval, $q, $localStorage, Api) {

        var factory = {};
        var user = false;

        var refreshInterval;
        var refreshTime = AppConfig.jwtRefreshTime;
        factory.startJwtRefreshInterval = function(runAtStart) {
            $interval.cancel(refreshInterval);
            refreshInterval = $interval(factory.getUser, refreshTime);
            if (runAtStart) {
                factory.getUser();
            }
        };

        factory.getUser = function(useCache) {
            var userDefer = $q.defer();
            if (useCache && user !== false) {
                userDefer.resolve(user);
            } else {
                Api.post('public/refresh-jwt').then(function (data) {
                    factory.setUserAndJwt(data);
                    userDefer.resolve(user);
                });
            }
            return userDefer.promise;
        };

        factory.setUserAndJwt = function(data) {
            user = null;
            delete $localStorage.user;
            delete $localStorage.jwt;
            if (data && data.success && data.success.user) {
                user = data.success.user;
                $localStorage.user = data.success.user;
                $localStorage.jwt = data.success.jwt;
            }
        };

        factory.setUser = function(userData) {
            user = userData;
        };

        factory.setUserFromLocalStorage = function() {
            user = $localStorage.user;
        };

        factory.getAttribute = function(attribute, defaultValue) {
            return user ? user[attribute] : defaultValue;
        };

        factory.isLoggedIn = function() {
            return user ? true : false;
        };

        factory.login = function(data) {
            return Api.post('public/login', data).then(function(data) {
                factory.setUserAndJwt(data);
                return data;
            });
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

        factory.logout = function(logoutUrl) {
            logoutUrl = logoutUrl ? logoutUrl : '/';
            return Api.post('public/logout').then(function(data) {
                factory.setUserAndJwt(data);
                factory.redirect(logoutUrl);
                return data;
            });
        };

        factory.register = function(data) {
            return Api.post('public/register', data).then(function(data) {
                factory.setUserAndJwt(data);
                return data;
            });
        };

        factory.redirect = function(url) {
            url = url ? url : '';
            $localStorage.loginUrl = '';
            $location.path(url).replace();
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