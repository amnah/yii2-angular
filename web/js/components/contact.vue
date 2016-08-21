
<template>
    <div>
        <h1>Contact</h1>

        <div v-if="success">
            <div class="alert alert-success">
                Thank you for contacting us [ {{ form.name }} ]. We will respond to you as soon as possible.
            </div>

            <p>
                Note that if you turn on the Yii debugger, you should be able
                to view the mail message on the mail panel of the debugger.
            </p>

            <p>
                If the application is in development mode, the email is not sent but saved as
                a file under <code>Yii::$app->mailer->fileTransportPath</code>.
                Please configure the <code>useFileTransport</code> property of the <code>mail</code>
                application component to be false to enable email sending.
            </p>

            <hr/>
        </div>

        <p>If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.</p>

        <div class="row">
            <div class="col-lg-5">
                <form id="contact-form" role="form" @submit.prevent="submit">

                    <div class="form-group" v-bind:class="{'has-error': errors.name}">
                        <label class="control-label" for="contact-form-name">Name</label>
                        <input type="text" id="contact-form-name" class="form-control" v-model.trim="form.name">
                        <p class="help-block help-block-error" v-if="errors.name">{{ errors.name[0] }}</p>
                    </div>
                    <div class="form-group" v-bind:class="{'has-error': errors.email}">
                        <label class="control-label" for="contact-form-email">Email</label>
                        <input type="text" id="contact-form-email" class="form-control" v-model.trim="form.email">
                        <p class="help-block help-block-error" v-if="errors.email">{{ errors.email[0] }}</p>
                    </div>
                    <div class="form-group" v-bind:class="{'has-error': errors.subject}">
                        <label class="control-label" for="contact-form-subject">Subject</label>
                        <input type="text" id="contact-form-subject" class="form-control" v-model.trim="form.subject">
                        <p class="help-block help-block-error" v-if="errors.subject">{{ errors.subject[0] }}</p>
                    </div>
                    <div class="form-group" v-bind:class="{'has-error': errors.body}">
                        <label class="control-label" for="contact-form-body">Body</label>
                        <textarea id="contact-form-body" class="form-control" rows="6" v-model.trim="form.body"></textarea>
                        <p class="help-block help-block-error" v-if="errors.body">{{ errors.body[0] }}</p>
                    </div>
                    <div class="form-group" v-bind:class="{'has-error': errors.captcha}" v-if="recaptchaShow">
                        <label class="control-label">Captcha</label>
                        <div id="contact-captcha"></div>
                        <p class="help-block help-block-error" v-if="errors.captcha">{{ errors.captcha[0] }}</p>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" :disabled="submitting">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</template>

<script>
import {setPageTitle} from '../functions.js'
import {post, reset, process} from '../api.js'
import recaptcha from '../recaptcha.js'
export default {
    name: 'contact',
    beforeCreate: function() {
        setPageTitle('Contact')
        recaptcha.render('contact-captcha')
    },
    data: function() {
        const user = this.$store.getters.user
        return {
            success: false,
            submitting: false,
            errors: {},
            form: {
                name: user ? user.username : '',
                email: user ? user.email : '',
                subject: '',
                body: '',
                captcha: '',
            },
            recaptchaShow: recaptcha.show()
        }
    },
    methods: {
        submit (e) {
            const vm = this
            reset(vm)
            if (!recaptcha.check(vm)) {
                return
            }
            post('public/contact', this.form).then(function(data) {
                process(vm, data)
                if (data.success) {
                    recaptcha.reset()
                }
            });
        }
    }
}
</script>