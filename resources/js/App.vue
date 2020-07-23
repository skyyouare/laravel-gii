<template>
    <div>
        <header-component></header-component>
        <div class="container">
            <div class="nav">
                <nav-component></nav-component>
            </div>
            <router-view></router-view>
        </div>
    </div>
</template>
<script scoped>

    export default {
        data(){
            return {}
        },
        components: {

        },
        computed: {},
        methods: {

        },
        mounted() {
        },
    }
</script>
