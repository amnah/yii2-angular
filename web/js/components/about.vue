
<template>
    <div>
        <h3>About</h3>
        <p>This is the about page.</p>
    </div>
</template>

<script>
import {setPageTitle} from '../functions.js'
export default {
    name: 'about',
    mounted: function() {
        setPageTitle('About')
    }
}
</script>