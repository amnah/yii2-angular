
// -------------------------------------------------------------
// Angular App
// -------------------------------------------------------------
var app = angular.module('App', [
    'ngRoute',
    'ngAnimate',
    'ngStorage',
    'ui.bootstrap'
]);

// -------------------------------------------------------------
// Routes
// -------------------------------------------------------------
app.config(['$routeProvider', function($routeProvider) {

    var staticPaths = ['about', 'contact', 'register', 'login'];

    for (var i=0; i<staticPaths.length; i++) {
        var path = staticPaths[i];
        $routeProvider.when('/' + path, {templateUrl: '/partials/' + path + '.html'});
    }
    $routeProvider.otherwise({templateUrl: '/partials/index.html'});
}]);

// -----------------------------------------------------------------
// Set up ajax requests and jwt processing
// -----------------------------------------------------------------
app.config(['$httpProvider', function($httpProvider) {

    // set up ajax requests
    // http://www.yiiframework.com/forum/index.php/topic/62721-yii2-and-angularjs-post/
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

    // add jwt into http headers
    $httpProvider.interceptors.push(['$localStorage', function($localStorage) {
        return {
            request: function(config) {
                if ($localStorage.jwt) {
                    config.headers.Authorization = 'Bearer ' + $localStorage.jwt;
                }
                return config;
            }
        };
    }]);
}]);

// -----------------------------------------------------------------
// Initialization
// -----------------------------------------------------------------
app.run(['User', function(User) {
    User.startJwtRefreshInterval(true);

    // attempt to set up user from local storage. this is faster than waiting for the automatic refresh
    User.loadFromLocalStorage();
}]);

// -----------------------------------------------------------------
// Api factory
// -----------------------------------------------------------------
app.factory('Api', ['$http', '$q', '$location', '$localStorage', function($http, $q, $location, $localStorage) {

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
            $localStorage.loginUrl = $location.path();
            $location.path('/login').replace();
        } else {
            var error = '[ ' + res.status + ' ] ' + (res.data.message || res.statusText);
            alert(error);
        }

        // calculate and return error msg
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
app.factory('User', ['$window', '$location', '$interval', '$q', '$localStorage', 'Api', function($window, $location, $interval, $q, $localStorage, Api) {

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
}]);

// -------------------------------------------------------------
// Nav controller
// -------------------------------------------------------------
app.controller('NavController', ['$scope', 'User', function($scope, User) {

    $scope.User = User;
    $scope.isCollapsed = true;

    $scope.logout = function() {
        User.logout().then(function(data) {
            // do something
        });
    };
}]);

// -------------------------------------------------------------
// Contact controller
// -------------------------------------------------------------
app.controller('ContactController', ['$scope', 'Api', 'User', function($scope, Api, User) {

    $scope.errors = {};
    $scope.sitekey = RECAPTCHA_SITEKEY;
    $scope.successName = '';
    $scope.ContactForm = {
        name: '',
        email: User.getAttribute('email'),
        subject: '',
        body: '',
        captcha: ''
    };

    // set up and store grecaptcha data
    var recaptchaId;
    var grecaptchaObj;
    if (RECAPTCHA_SITEKEY) {
        User.getRecaptcha().then(function (grecaptcha) {
            grecaptchaObj = grecaptcha;
            recaptchaId = grecaptcha.render("contact-captcha", {sitekey: $scope.sitekey});
        });
    }

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
                $scope.successName = $scope.ContactForm.name;
                $scope.errors = false;
                if (recaptchaId) {
                    recaptchaId = grecaptchaObj.reset(recaptchaId);
                }
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
    $scope.loginUrl = User.getLoginUrl();
    $scope.LoginForm = {
        email: '',
        password: '',
        rememberMe: true
    };

    // process form submit
    $scope.submit = function() {
        $scope.errors = {};
        $scope.submitting  = true;
        User.login($scope.LoginForm).then(function(data) {
            $scope.submitting  = false;
            if (data.success) {
                User.startJwtRefreshInterval();
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
    $scope.successName = '';
    $scope.RegisterForm = {
        email: '',
        password: '',
        captcha: ''
    };

    // set up and store grecaptcha data
    var recaptchaId;
    var grecaptchaObj;
    if (RECAPTCHA_SITEKEY) {
        User.getRecaptcha().then(function(grecaptcha) {
            grecaptchaObj = grecaptcha;
            recaptchaId = grecaptcha.render("register-captcha", {sitekey: RECAPTCHA_SITEKEY});
        });
    }

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
                $scope.successName = data.success.user.email;
                $scope.errors = false;
                User.startJwtRefreshInterval();
            } else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);