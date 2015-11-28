(function () {
    'use strict';

    angular
        .module('app')
        .factory('Auth', Auth);

    // @ngInject
    function Auth($window, $location, $interval, $q, $localStorage, Config, Api) {

        var factory = {};
        var user = false;

        var jwtInterval;
        var jwtIntervalTime = Config.jwtIntervalTime;
        factory.startTokenRenewInterval = function(runAtStart) {
            $interval.cancel(jwtInterval);
            jwtInterval = $interval(factory.getUser, jwtIntervalTime);
            if (runAtStart) {
                factory.getUser();
            }
        };

        factory.getUser = function(useCache) {
            var userDefer = $q.defer();
            if (useCache && user !== false) {
                userDefer.resolve(user);
            } else {
                Api.get('public/renew-token').then(function(data) {
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
                if (!Config.jwtCookie) {
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

        factory.getRefreshToken = function() {
            // note: this will return nothing if the frontend client is using cookies
            return $localStorage.refreshToken;
        };

        factory.requestRefreshToken = function() {
            return Api.get('public/request-refresh-token').then(function(data) {
                if (!Config.jwtCookie) {
                    $localStorage.refreshToken = data.success;
                }
                return data;
            });
        };

        factory.removeRefreshToken = function(callApi) {
            // remove token from local storage and cookie
            delete $localStorage.refreshToken;
            if (callApi) {
                return Api.get('public/remove-refresh-token').then(function(data) {
                    return data;
                });
            }
        };

        factory.useRefreshToken = function() {
            var params = $localStorage.refreshToken ? {refreshToken: $localStorage.refreshToken} : {};
            return Api.get('public/use-refresh-token', params).then(function(data) {
                factory.setUserAndToken(data);
                if (!user) {
                    factory.removeRefreshToken();
                }
                return data;
            });
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

        factory.setLoginUrl = function(url) {
            $localStorage.loginUrl = url;
            return this;
        };

        factory.clearLoginUrl = function() {
            delete $localStorage.loginUrl;
            return this;
        };

        factory.redirect = function(url) {
            url = url ? url : '';
            $location.path(url).replace();
            return this;
        };

        // get login url via (1) local storage or (2) fallbackUrl
        factory.getLoginUrl = function(fallbackUrl) {
            return $localStorage.loginUrl || fallbackUrl || '';
        };

        factory.logout = function(logoutUrl) {
            return Api.post('public/logout').then(function(data) {
                factory.setUserAndToken(data);
                factory.removeRefreshToken();
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