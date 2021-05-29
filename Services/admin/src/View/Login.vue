<template>
    <div id="container">
        <div class="panel wow fadeInUp">
            <transition name="fade" mode="out-in">
                <form v-if="!isLoading">
                    <h3>Panel sterowania CMC</h3>
                    <label for="loginInput">Adres email</label>
                    <input id="loginInput" v-model="loginForm.email"  type="email" placeholder="Adres e-mail"/>

                    <label for="passwordInput">Hasło</label>
                    <input id="passwordInput" v-model="loginForm.password" type="password" placeholder="Hasło"/>

                    <button class="button-p button-md m-t-3" @click.prevent="login" size="md" variant="outline-light">Zaloguj się</button>
                </form>
                <loader v-else></loader>
            </transition>
        </div>
    </div>
</template>

<script>

    export default {
        data: () => (
            {
                loginForm:{
                    email: null,
                    password: null,
                    place: "adminPanel"
                },
                isLoading: false
            }
        ),
        methods: {
            login () {
                try {
                    let valid = new window.Validator({
                        "adres email": this.loginForm.email,
                        "hasło": this.loginForm.password
                    });
                    valid.get("adres email").length(3,50);
                    valid.get("hasło").length(5,50);
                }catch (e) {
                    return
                }

                this.isLoading = true;

                axios.post('/auth/login', this.loginForm)
                    .then((response) => {
                        window.AuthMiddleware.login(response.data);
                        this.$router.push( (this.$route.query.redirect && this.$route.query.redirect.length > 0)? this.$route.query.redirect : '/');
                        this.isLoading = false;
                    })
                    .catch((error) => {
                        setTimeout(()=>{
                            this.isLoading = false;
                        },1000);
                    });
            }
        },
        mounted: function(){
        }
    }
</script>

<style scoped lang="scss">
    #container{
        min-height: 100vh;
        width: 100vw;
        background-size: cover;
        background: url("../assets/login.jpeg") center;
        display: flex;
        justify-content: center;
        align-items: center;

        form{
            display: flex;
            flex-direction: column;
            
            h3{
                text-align: center;
            }
        }
    }
</style>
