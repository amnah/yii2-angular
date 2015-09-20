
// -------------------------------------------------------------
// Angular App
// -------------------------------------------------------------
var app = angular.module('App', [
    'ngRoute'
]);

// -------------------------------------------------------------
// Routes
// -------------------------------------------------------------
app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
        when('/about', {
            templateUrl: '/partials/about.html'
        }).
        when('/contact', {
            templateUrl: '/partials/contact.html'
        }).
        when('/register', {
            templateUrl: '/partials/register.html'
        }).
        when('/login', {
            templateUrl: '/partials/login.html'
        }).
        otherwise({
            templateUrl: '/partials/index.html'
        });
}]);

// -----------------------------------------------------------------
// Set up ajax requests and jwt processing
// -----------------------------------------------------------------
app.config(['$httpProvider', function($httpProvider) {

    // set up ajax requests
    // http://www.yiiframework.com/forum/index.php/topic/62721-yii2-and-angularjs-post/
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

    // add jwt into http headers
    $httpProvider.interceptors.push(['$window', function($window) {
        return {
            request: function(config) {
                if ($window.localStorage.jwt) {
                    config.headers.Authorization = 'Bearer ' + $window.localStorage.jwt;
                }
                return config;
            }
        };
    }]);
}]);

// -----------------------------------------------------------------
// Api factory
// -----------------------------------------------------------------
app.factory('Api', ['$http', '$q', '$window', '$location', function($http, $q, $window, $location) {

    var factory = {};
    var apiUrl = API_URL;

    // process ajax success/error calls
    var processAjaxSuccess = function(res) {
        return res.data;
    };
    var processAjaxError = function(res) {

        // process 401 by redirecting to login page
        // otherwise just alert the error
        if (res.status == 401) {
            $window.localStorage.loginUrl = $location.path();
            $location.path('/login').replace();
        } else {
            alert(error);
        }

        // calculate and return error msg
        var error = '[ ' + res.status + ' ] ' + (res.data.message || res.statusText);
        return $q.reject(res);
    };

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
}]);

// -----------------------------------------------------------------
// User factory
// -----------------------------------------------------------------
app.factory('User', ['$window', '$location', '$interval', '$q', 'Api', function($window, $location, $interval, $q, Api) {

    var factory = {};

    var user;

    // set minimum of once per minute just in case
    var minRefreshTime = 1000*60;
    var refreshTime = JWT_REFRESH_TIME;
    if (refreshTime < minRefreshTime) {
        refreshTime = minRefreshTime;
    }
    var refreshInterval;

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
        var loginUrl = $window.localStorage.loginUrl;
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
        $window.localStorage.loginUrl = '';
        $location.path(url).replace();
    };

    factory.startJwtRefreshInterval = function() {
        refreshInterval = $interval(factory.doJwtRefresh, refreshTime);
    };

    factory.cancelJwtRefreshInterval = function() {
        $interval.cancel(refreshInterval);
    };

    factory.doJwtRefresh = function() {
        var jwtRefresh = $window.localStorage.jwtRefresh;
        if (jwtRefresh) {
            Api.post('public/jwt-refresh', {jwtRefresh: jwtRefresh}).then(function (data) {
                factory.setUser(data);
            });
        }
    };

    factory.start = function() {
        refreshInterval = $interval(factory.refresh, refreshTime);
    };

    factory.setUser = function(data) {
        if (data && data.success && data.success.user) {
            user = data.success.user;
            $window.localStorage.jwt = data.success.jwt;
            $window.localStorage.jwtRefresh = data.success.jwtRefresh;
        } else {
            user = null;
            $window.localStorage.jwt = '';
            $window.localStorage.jwtRefresh = '';
        }
    };

    factory.login = function(data) {
        return Api.post('public/login', data).then(function(data) {
            factory.setUser(data);
            return data;
        });
    };

    factory.logout = function() {
        return Api.post('public/logout').then(function(data) {
            factory.setUser(data);
            return data;
        });
    };

    factory.register = function(data) {
        return Api.post('public/register', data).then(function(data) {
            factory.setUser(data);
            return data;
        });
    };

    // set up recaptcha
    var recaptchaDefer = $q.defer();
    factory.getRecaptcha = function() {
        return recaptchaDefer.promise;
    };
    $window.recaptchaLoaded = function() {
        recaptchaDefer.resolve($window.grecaptcha);
    };

    // initialize jwt intervals
    factory.doJwtRefresh();
    factory.startJwtRefreshInterval();

    return factory;
}]);

// -------------------------------------------------------------
// Nav controller
// -------------------------------------------------------------
app.controller('NavController', ['$scope', 'User', function($scope, User) {

    $scope.User = User;

    $scope.logout = function() {
        User.logout().then(function(data) {
            User.setUser(null);
        });

    };
}]);

// -------------------------------------------------------------
// Contact controller
// -------------------------------------------------------------
app.controller('ContactController', ['$scope', 'Api', 'User', function($scope, Api, User) {

    $scope.errors = {};
    $scope.sitekey = RECAPTCHA_SITEKEY;
    $scope.ContactForm = {
        name: User.getAttribute('username'),
        email: User.getAttribute('email'),
        subject: '',
        body: '',
        captcha: ''
    };

    // set up and store grecaptcha data
    var recaptchaId;
    var grecaptchaObj;
    User.getRecaptcha().then(function(grecaptcha) {
        grecaptchaObj = grecaptcha;
        if (RECAPTCHA_SITEKEY) {
            recaptchaId = grecaptcha.render("contact-captcha", {sitekey: $scope.sitekey});
        }
    });

    // process form submit
    $scope.submit = function() {

        // check captcha before making POST request
        if (RECAPTCHA_SITEKEY) {
            $scope.ContactForm.captcha = grecaptchaObj.getResponse(recaptchaId);
            if (!$scope.ContactForm.captcha) {
                $scope.errors.captcha = ['Invalid captcha'];
                return false;
            }
        }

        $scope.errors = {};
        $scope.submitting  = true;
        Api.post('public/contact', $scope.ContactForm).then(function(data) {
            $scope.submitting  = false;
            if (data.success) {
                $scope.errors = false;
            } else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);

// -------------------------------------------------------------
// Login controller
// -------------------------------------------------------------
app.controller('LoginController', ['$scope', 'User', function($scope, User) {

    $scope.errors = {};
    $scope.LoginForm = {
        email: '',
        password: '',
        rememberMe: true
    };

    $scope.loginUrl = User.getLoginUrl();

    // process form submit
    $scope.submit = function() {
        $scope.errors = {};
        $scope.submitting  = true;
        User.login($scope.LoginForm).then(function(data) {
            $scope.submitting  = false;
            if (data.success) {
                User.redirect($scope.loginUrl);
            } else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);

// -------------------------------------------------------------
// Register controller
// -------------------------------------------------------------
app.controller('RegisterController', ['$scope', 'User', function($scope, User) {

    $scope.errors = {};
    $scope.sitekey = RECAPTCHA_SITEKEY;
    $scope.RegisterForm = {
        email: '',
        newPassword: '',
        captcha: ''
    };

    // set up and store grecaptcha data
    var recaptchaId;
    var grecaptchaObj;
    User.getRecaptcha().then(function(grecaptcha) {
        grecaptchaObj = grecaptcha;
        if (RECAPTCHA_SITEKEY) {
            recaptchaId = grecaptcha.render("register-captcha", {sitekey: RECAPTCHA_SITEKEY});
        }
    });

    // process form submit
    $scope.submit = function() {

        // check captcha before making POST request
        if (RECAPTCHA_SITEKEY) {
            $scope.RegisterForm.captcha = grecaptchaObj.getResponse(recaptchaId);
            if (!$scope.RegisterForm.captcha) {
                $scope.errors.captcha = ['Invalid captcha'];
                return false;
            }
        }

        $scope.errors = {};
        $scope.submitting  = true;
        User.register($scope.RegisterForm).then(function(data) {
            $scope.submitting  = false;
            if (data.success) {
                User.redirect();
            } else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);