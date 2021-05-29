<template>
    <main>
        <div class="row">
            <div class="panel col wow fadeInUp">
                <div class="panel-header">
                    <h3>Lista użytkowników</h3>
                    <button class="panelActionTop" @click="$router.push('/user/add')">Dodaj</button>
                </div>
                <table class="sortable-theme-light" data-sortable>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa</th>
                        <th>Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="user in users">
                        <td>{{user.id}}</td>
                        <td>{{user.firstname}} {{user.lastname}}</td>
                        <td class="action">
                            <div @click='editUser(user.id)'>
                                <i class="btn-i fas fa-edit"></i>
                            </div>
                            <div @click='deleteUser(user.id)'>
                                <i class="btn-i fas fa-trash"></i>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</template>

<script>
    export default {
        name: "Users",
        data: () => (
            {
                users: []
            }
        ),
        methods: {
            getUsers(){
                axios.get(`/user`)
                    .then((response) => {
                        this.users = response.data;
                    })
            },
            editUser(id){
                this.$router.push(`/user/${id}`);
            },
            deleteUser(id){
                axios.delete(`/user/${id}`)
                    .then((response) => {
                        this.getUsers();
                    })
            }
        },
        mounted: function(){
            this.getUsers();
        }
    }
</script>

<style scoped>

</style>