(function () {
    'use strict';

    angular
        .module('app')
        .factory('Auth', Auth);

    // @ngInject
    function Auth($window, $location, $interval, $q, $localStorage, Config, Api) {

        var factory = {};
        var user = false;

        var renewInterval;
        var renewTime = Config.tokenRenewInterval;
        factory.startTokenRenewInterval = function(runAtStart) {
            $interval.cancel(renewInterval);
            renewInterval = $interval(factory.getUser, renewTime);
            if (runAtStart) {
                factory.getUser();
            }
        };

        factory.getUser = function(useCache) {
            var userDefer = $q.defer();
            if (useCache && user !== false) {
                userDefer.resolve(user);
            } else {
                Api.get('public/renew-token').then(function (data) {
                    factory.setUserAndToken(data);
                    userDefer.resolve(user);
                });
            }
            return userDefer.promise;
        };

        factory.setUserAndToken = function(data) {
            user = null;
            delete $localStorage.user;
            delete $localStorage.token;

            // set data if valid
            if (data && data.success && data.success.user) {
                user = data.success.user;
                $localStorage.user = data.success.user;

                // set token only if we're not using cookies
                if (!Config.useCookie) {
                    $localStorage.token = data.success.token;
                }
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
                factory.setUserAndToken(data);
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
                factory.setUserAndToken(data);
                factory.redirect(logoutUrl);
                return data;
            });
        };

        factory.register = function(data) {
            return Api.post('public/register', data).then(function(data) {
                factory.setUserAndToken(data);
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