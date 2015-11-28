(function () {
    'use strict';

    angular
        .module('app')
        .controller('RegisterCtrl', RegisterCtrl);

    // @ngInject
    function RegisterCtrl(Config, Auth) {

        var vm = this;
        vm.errors = {};
        vm.sitekey = Config.recaptchaSitekey;
        vm.RegisterForm = { rememberMe: true, jwtCookie: Config.jwtCookie };

        // set up and store grecaptcha data
        var recaptchaId;
        var grecaptchaObj;
        if (vm.sitekey) {
            Auth.getRecaptcha().then(function (grecaptcha) {
                grecaptchaObj = grecaptcha;
                recaptchaId = grecaptcha.render("register-captcha", {sitekey: vm.sitekey});
            });
        }

        // process form submit
        vm.submit = function() {
            // check captcha before making POST request
            vm.errors = {};
            vm.RegisterForm.captcha = vm.sitekey ? grecaptchaObj.getResponse(recaptchaId) : '';
            if (vm.sitekey && !vm.RegisterForm.captcha) {
                vm.errors.captcha = ['Invalid captcha'];
                return false;
            }

            vm.submitting  = true;
            Auth.register(vm.RegisterForm).then(function(data) {
                vm.submitting  = false;
                if (data.success) {
                    vm.errors = false;
                    recaptchaId = vm.sitekey ? grecaptchaObj.reset(recaptchaId) : null;
                } else if (data.errors) {
                    vm.errors = data.errors;
                }
            });
        };
    }

})();