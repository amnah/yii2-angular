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

        factory.startJwtRefreshInterval = function(runAtStart) {
            $interval.cancel(refreshInterval);
            refreshInterval = $interval(factory.getUser, refreshTime);
            if (runAtStart) {
                factory.getUser();
            }
        };

        factory.getUser = function(useCache) {
            // use cache if specified and valid. otherwise make api call to resolve
            var userDefer = $q.defer();
            var jwtRefresh = $localStorage.jwtRefresh;
            if (useCache && user) {
                userDefer.resolve(user);
            } else if (jwtRefresh) {
                Api.post('public/jwt-refresh', {jwtRefresh: jwtRefresh}).then(function (data) {
                    factory.setUserAndJwt(data);
                    userDefer.resolve(user);
                });
            } else {
                userDefer.resolve(null);
            }
            return userDefer.promise;
        };

        factory.setUser = function(userData) {
            user = userData;
        };

        factory.setUserAndJwt = function(data) {
            user = null;
            delete $localStorage.user;
            delete $localStorage.jwt;
            delete $localStorage.jwtRefresh;
            if (data && data.success && data.success.user) {
                user = data.success.user;
                $localStorage.user = data.success.user;
                $localStorage.jwt = data.success.jwt;
                $localStorage.jwtRefresh = data.success.jwtRefresh;
            }
        };

        factory.loadFromLocalStorage = function() {
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

        factory.authRedirect = function(url) {
            // set empty data so it will propagate to other controllers using this User factory
            factory.setUserAndJwt(null);
            $localStorage.loginUrl = url ? url : $location.path();
            $location.path('/login').replace();
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