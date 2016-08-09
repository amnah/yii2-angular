
<template>

    <div>
        <!-- put this v-if here because we cant put vue expressions on the above root div -->
        <div v-if="loaded">

            <h1>Profile</h1>

            <div class="success" v-if="success">
                <div class="alert alert-success">
                    <p>Profile saved</p>
                </div>
            </div>

            <p>This is the Profile page. Note that you need to be logged in to view this.</p>

            <form id="profile-form" class="form-horizontal" role="form" @submit.prevent="submit">

                <div class="form-group" v-bind:class="{'has-error': errors.full_name}">
                    <label class="col-lg-1 control-label" for="profile-form-full_name">Full name</label>
                    <div class="col-lg-3">
                        <input type="text" id="profile-form-full_name" class="form-control" v-model.trim="form.full_name">
                    </div>
                    <div class="col-lg-8">
                        <p class="help-block help-block-error" v-if="errors.full_name">{{ errors.full_name[0] }}</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <button type="submit" class="btn btn-primary" :disabled="submitting">Save</button>
                    </div>
                </div>

            </form>

        </div>

    </div>

</template>

<script>
import {setPageTitle, getConfig} from '../functions.js'
import {get, post, reset, process} from '../api.js'
import router from '../router.js'
export default {
    name: 'profile',
    beforeCreate: function() {
        const vm = this
        setPageTitle('Profile')
        get('user/profile').then(function(data) {
            vm.loaded = true
            if (data.success) {
                vm.form = data.success.profile
            }
        });
    },
    data () {
        return {
            success: false,
            submitting: false,
            errors: {},
            loaded: false,
            form: {}
        }
    },
    methods: {
        submit (e) {
            const vm = this
            reset(vm)
            post('user/profile', vm.form).then(function(data) {
                process(vm, data)
            });
        }
    }
}
</script>