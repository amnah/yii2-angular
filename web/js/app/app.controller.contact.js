(function () {
    'use strict';

    angular
        .module('app')
        .controller('ContactCtrl', ContactCtrl);

    // @ngInject
    function ContactCtrl(Config, AjaxHelper, Api, Auth) {

        var vm = this;
        vm.sitekey = Config.recaptchaSitekey;
        vm.ContactForm = { name: Auth.getAttribute('username'), email: Auth.getAttribute('email') };

        // set up and store grecaptcha data
        var recaptchaId;
        var grecaptchaObj;
        if (vm.sitekey) {
            Auth.getRecaptcha().then(function (grecaptcha) {
                grecaptchaObj = grecaptcha;
                recaptchaId = grecaptcha.render('contact-captcha', {sitekey: vm.sitekey});
            });
        }

        // process form submit
        vm.submit = function() {
            // check captcha before making POST request
            vm.errors = {};
            vm.ContactForm.captcha = vm.sitekey ? grecaptchaObj.getResponse(recaptchaId) : '';
            if (vm.sitekey && !vm.ContactForm.captcha) {
                vm.errors.captcha = ['Invalid captcha'];
                return false;
            }

            AjaxHelper.reset(vm);
            Api.post('public/contact', vm.ContactForm).then(function(data) {
                AjaxHelper.process(vm, data);
                if (data.success) {
                    recaptchaId = vm.sitekey ? grecaptchaObj.reset(recaptchaId) : null;
                }
            });
        };
    }

})();