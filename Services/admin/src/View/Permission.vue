<template>
    <main>
        <div class="row">
            <role-panel  class="panel col wow fadeInUp"></role-panel>
            <permission-panel class="panel col-2 wow fadeInUp"></permission-panel>
        </div>
    </main>
</template>

<script>

    import RolePanel from './Permission/RolePanel';
    import PermissionPanel from './Permission/PermissionPanel';

    export default {
        name: "Permission",
        components: {
            "role-panel": RolePanel,
            "permission-panel": PermissionPanel
        }
    }
</script>

<style scoped>

</style>