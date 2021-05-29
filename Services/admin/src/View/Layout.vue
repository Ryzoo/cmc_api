<template>
    <main id="container">
        <div class="darkerTop"></div>
        <div class="pageContainer">
            <header class="wow fadeInDown">
                <div class="userData">
                    <router-link to="/">
                        <img :src="user.profile_img" :alt="user.firstname">
                        <h2>{{user.firstname}} {{user.lastname}}</h2>
                    </router-link>
                </div>
                <nav>
                    <router-link to="database">Baza danych</router-link>
                    <router-link to="permission">Uprawnienia</router-link>
                    <router-link to="cron">Zadania CRON</router-link>
                    <a href="" @click.prevent="logout">Wyloguj</a>
                </nav>
            </header>
            <div class="pageContent">
                <transition name="fade" mode="out-in">
                    <router-view></router-view>
                </transition>
            </div>
        </div>
    </main>
</template>

<script>
    export default {
        name: "Dashboard",
        data: function(){
            return{
                user: window.AuthMiddleware.user
            }
        },
        methods: {
            logout(){
                window.AuthMiddleware.logout();
                this.$router.push('/login');
            },
            checkElOpacityToTop(){
                let parentOffset = $(".pageContent").offset().top;
                $(".pageContent").first().find('.col, .col-2').each(function(){
                    let childOffset = ((1.2*$(this).height())+$(this).offset().top - parentOffset);
                    let childHeight = $(this).height();

                    let opacity = 0;

                    if(childOffset > 0){
                        opacity = (childOffset / childHeight);
                        if(opacity > 1 ) opacity = 1;
                    }

                    $(this).css('opacity',opacity);
                });
            },
            checkElOpacity(){
                this.checkElOpacityToTop();
            }
        },
        mounted(){
            this.checkElOpacity();

            $(".pageContent").first().scroll(()=>{
                this.checkElOpacity();
            });

            $(window).resize(()=>{
                this.checkElOpacity();
            });

        },
        beforeDestroy(){
            $(window).off("resize");
            $(".pageContent").first().off("scroll");
        }
    }
</script>

<style lang="scss" scoped>
    #container{
        z-index: 1;
        min-height: 100vh;
        width: 100vw;
        background-size: cover;
        background: url("../assets/login.jpeg") top;
        display: flex;
        position: relative;
        max-height: 100vh;

        .pageContent{
            box-sizing: border-box;
            padding: 100px 15px;
            width: 100%;
            overflow-y: scroll;
            max-height: calc(100vh - 160px);

            &::-webkit-scrollbar {
                display: none;
            }
        }
    }
</style>