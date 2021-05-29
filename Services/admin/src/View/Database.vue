<template>
    <main>
        <div class="row">
            <table-action-panel @refresh="getStats" :stats="stats" class="panel col wow fadeInUp"></table-action-panel>
        </div>
        <div class="row">
            <table-stat-panel @refresh="getStats" :tables="stats.tables" class="col wow fadeInUp"></table-stat-panel>
        </div>
    </main>
</template>

<script>
    import TableStatPanel from './Database/TableStatPanel.vue';
    import TableActionPanel from './Database/TableActionPanel.vue';


    export default {
        name: "Database",
        components: {
            "table-stat-panel": TableStatPanel,
            "table-action-panel": TableActionPanel
        },
        data(){
            return {
                stats: {
                    tables: [],
                    size: 0,
                    name: 'default'
                }
            }
        },
        methods: {
            getStats(){
                axios.get(`/stats/database`)
                    .then((response) => {
                        this.stats = response.data;
                    })
            }
        },
        mounted(){
            this.getStats();
        }
    }
</script>

<style scoped>

</style>