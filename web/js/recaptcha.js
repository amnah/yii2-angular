
import {getConfig} from './functions.js'

// process and maintain recaptcha state
let recaptchaObj, recaptchaId
const recaptchaDefer = $.Deferred()
window.recaptchaLoaded = function() {
    recaptchaObj = window.grecaptcha
    recaptchaDefer.resolve(recaptchaObj)
}
function getRecaptcha() {
    return recaptchaDefer
}


export default {

    getRecaptcha: getRecaptcha,

    render: function(elementId, renderOptions) {
        const recaptchaSitekey = getConfig('recaptchaSitekey')
        if (!recaptchaSitekey) {
            return
        }

        getRecaptcha().then(function(grecaptcha) {
            renderOptions = $.extend({sitekey: recaptchaSitekey}, renderOptions)
            recaptchaId = grecaptcha.render(elementId, renderOptions);
        });
    },

    show: function() {
        return !!getConfig('recaptchaSitekey')
    },

    check: function(vm) {
        if (!recaptchaObj) {
            return true
        }

        vm.form.captcha = recaptchaObj.getResponse(recaptchaId)
        if (!vm.form.captcha) {
            vm.errors.captcha = ['Invalid captcha']
            vm.submitting = false
        }
        return vm.form.captcha
    },

    reset: function() {
        if (recaptchaObj) {
            recaptchaId = recaptchaObj.reset(recaptchaId)
        }
    }
}

