(function () {
    'use strict';

    angular
        .module('app')
        .factory('Auth', Auth);

    // @ngInject
    function Auth($window, $location, $routeParams, $interval, $q, $localStorage, Config, Api) {

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
                Api.get('auth/renew-token').then(function(data) {
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
                factory.startTokenRenewInterval();

                // set local storage data
                //   note: set token only if we're not using cookies
                $localStorage.user = data.success.user;
                if (!Config.jwtCookie) {
                    $localStorage.token = data.success.token;
                }
            }

            // return user so we know if successful or not
            return user;
        };

        factory.setUser = function(userData) {
            user = userData;
        };

        factory.setUserFromLocalStorage = function() {
            user = $localStorage.user;
            return user;
        };

        factory.getRefreshToken = function() {
            // note: this will return nothing if the frontend client is using cookies
            return $localStorage.refreshToken;
        };

        factory.requestRefreshToken = function() {
            return Api.get('auth/request-refresh-token').then(function(data) {
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
                return Api.get('auth/remove-refresh-token').then(function(data) {
                    return data;
                });
            }
        };

        factory.useRefreshToken = function() {
            var params = $localStorage.refreshToken ? {refreshToken: $localStorage.refreshToken} : {};
            return Api.get('auth/use-refresh-token', params).then(function(data) {
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
            return Api.post('auth/login', data);
        };

        factory.loginEmail = function(data) {
            return Api.post('auth/login-email', data);
        };

        factory.loginCallback = function(userData) {
            var token = $routeParams.token;
            var jwtCookie = Config.jwtCookie;
            var url = 'auth/login-callback?token=' + token + '&jwtCookie=' + jwtCookie;
            if (userData) {
                return Api.post(url, userData);
            } else {
                return Api.get(url);
            }
        };

        factory.register = function(data) {
            return Api.post('auth/register', data);
        };

        factory.confirm = function() {
            var token = $routeParams.token;
            return Api.get('auth/confirm', {token: token});
        };

        factory.setLoginUrl = function(url) {
            $localStorage.loginUrl = url || $location.path();
            return this;
        };

        factory.clearLoginUrl = function() {
            delete $localStorage.loginUrl;
            return this;
        };

        factory.redirect = function(url) {
            url = url ? url : '';
            $location.path(url).replace();

            // clear get params and login url
            // @link http://stackoverflow.com/a/26336011
            $location.search({});
            factory.clearLoginUrl();
            return this;
        };

        // get login url via (1) local storage or (2) fallbackUrl
        factory.getLoginUrl = function(fallbackUrl) {
            return $localStorage.loginUrl || fallbackUrl || '';
        };

        factory.logout = function(logoutUrl) {
            return Api.post('auth/logout').then(function(data) {
                factory.setUserAndToken(data);
                factory.removeRefreshToken();
                factory.redirect(logoutUrl);
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