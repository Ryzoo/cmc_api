<template>
    <main>
        <div class="row">
            <last-user-panel class="col-2 wow fadeInUp"></last-user-panel>
            <div class="panel col wow fadeInUp"></div>
        </div>
        <div class="row">
            <logs-panel class="col wow fadeInUp"></logs-panel>
        </div>
    </main>
</template>

<script>
    import LogsPanel from './Dashboard/LogsPanel.vue';
    import LastUserPanel from './Dashboard/LastUserPanel.vue';

    export default {
        name: "Users",
        components: {
            "logs-panel": LogsPanel,
            "last-user-panel": LastUserPanel
        }
    }
</script>

<style scoped>

</style>