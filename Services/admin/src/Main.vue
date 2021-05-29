<template>
    <transition name="fade" mode="out-in">
        <router-view></router-view>
    </transition>
</template>

<script>

    export default {
        data: () => {
            return {};
        },
        methods: {
            logout() {
                window.AuthMiddleware.logout();
                this.$router.push('/login');
            }
        },
        mounted: function () {
            wow.init();
        },
        computed: {}
    }
</script>

<style lang="scss">
</style>
