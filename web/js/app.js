
// -------------------------------------------------------------
// Angular App
// -------------------------------------------------------------
var app = angular.module('App', [
    'ngRoute'
]);

// set up ajax requests
// http://www.yiiframework.com/forum/index.php/topic/62721-yii2-and-angularjs-post/
app.config(['$httpProvider', function($httpProvider) {
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
}]);

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
// Api Factory
// -----------------------------------------------------------------
app.factory('Api', ['$http', '$q', '$window', function ($http, $q, $window) {

    var factory = {};

    // set api url
    var apiUrl = API_URL;

    // process $http ajax success/error
    var processAjaxSuccess = function (res) {
        return res.data;
    };
    var processAjaxError = function (res) {
        var error = '[ ' + res.status + ' ] ' + (res.data.message || res.statusText);
        alert(error);
        return {error: error};
    };

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

    // set up recaptcha
    var deferred = $q.defer();
    factory.getRecaptcha = function() {
        return deferred.promise;
    };
    $window.recaptchaLoaded = function() {
        deferred.resolve($window.grecaptcha);
    };

    return factory;
}]);

// -----------------------------------------------------------------
// User Factory
// -----------------------------------------------------------------
app.factory('User', ['Api', function (Api) {

    var factory = {};

    // get/store user
    var user;
    Api.get('public/user').then(function(data) {
        user = data.success;
    });

    factory.getAttributes = function() {
        return user ? user : null;
    };

    factory.getAttribute = function(attribute, defaultValue) {
        return user ? user[attribute] : defaultValue;
    };

    factory.isLoggedIn = function() {
        return user ? true : false;
    };

    factory.register = function(data) {
        return Api.post('public/register', data).then(function(data) {
            user = data.success;
            return data;
        });
    };

    factory.login = function(data) {
        return Api.post('public/login', data).then(function(data) {
            user = data.success;
            return data;
        });
    };

    factory.logout = function() {
        return Api.post('public/logout').then(function(data) {
            user = data.success ? null : user;
            return data;
        });
    };

    return factory;
}]);

// -------------------------------------------------------------
// Nav controller
// -------------------------------------------------------------
app.controller('NavController', ['$scope', 'User', function($scope, User) {

    $scope.User = User;

    $scope.logout = function() {
        User.logout();
    };
}]);

// -------------------------------------------------------------
// Contact controller
// -------------------------------------------------------------
app.controller('ContactController', ['$scope', 'Api', function($scope, Api) {

    $scope.errors = {};
    $scope.sitekey = RECAPTCHA_SITEKEY;
    $scope.ContactForm = {
        name: '',
        email: '',
        subject: '',
        body: '',
        captcha: ''
    };

    // set up and store grecaptcha data
    var recaptchaId;
    var grecaptchaObj;
    Api.getRecaptcha().then(function(grecaptcha) {
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
            }
            else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);

// -------------------------------------------------------------
// Login controller
// -------------------------------------------------------------
app.controller('LoginController', ['$scope', '$location', 'User', function($scope, $location, User) {

    $scope.errors = {};
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
                $location.path('/');
            }
            else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);

// -------------------------------------------------------------
// Register controller
// -------------------------------------------------------------
app.controller('RegisterController', ['$scope', '$location', 'User', 'Api', function($scope, $location, User, Api) {

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
    Api.getRecaptcha().then(function(grecaptcha) {
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
                $location.path('/');
            }
            else if (data.errors) {
                $scope.errors = data.errors;
            }
        });
    };
}]);