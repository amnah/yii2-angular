(function () {
    'use strict';

    angular
        .module('app')
        .controller('RegisterCtrl', RegisterCtrl);

    // @ngInject
    function RegisterCtrl(Config, Auth) {

        var vm = this;
        vm.sitekey = Config.recaptchaSitekey;
        vm.User = { rememberMe: true, jwtCookie: Config.jwtCookie };

        vm.status = 0; // 0 = not registered
                       // 1 = registered but needs email confirmation
                       // 2 = registered and ready to login

        // set up and store grecaptcha data
        var recaptchaId;
        var grecaptchaObj;
        if (vm.sitekey) {
            Auth.getRecaptcha().then(function (grecaptcha) {
                grecaptchaObj = grecaptcha;
                recaptchaId = grecaptcha.render('register-captcha', {sitekey: vm.sitekey});
            });
        }

        // process form submit
        vm.submit = function() {
            // check captcha before making POST request
            vm.errors = {};
            vm.User.captcha = vm.sitekey ? grecaptchaObj.getResponse(recaptchaId) : '';
            if (vm.sitekey && !vm.User.captcha) {
                vm.errors.captcha = ['Invalid captcha'];
                return false;
            }

            vm.submitting  = true;
            Auth.register(vm.User).then(function(data) {
                vm.submitting  = false;
                if (data.success) {
                    vm.errors = false;
                    vm.status = data.success.userToken ? 1 : 2;
                    recaptchaId = vm.sitekey ? grecaptchaObj.reset(recaptchaId) : null;
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };
    }

})();