import Vue from 'vue';
import Router from 'vue-router';

import Login from '@/View/Login';
import Main from '@/Main';
import Dashboard from '@/View/Dashboard';
import Layout from '@/View/Layout';
import Users from '@/View/Users';
import UserAdd from '@/View/Users/UserAdd';
import Database from '@/View/Database';
import License from '@/View/License';
import LicenseAdd from '@/View/License/LicenseAdd';
import UserLicenseAdd from '@/View/License/UserLicenseAdd';
import Permission from '@/View/Permission';
import PermissionAdd from '@/View/Permission/PermissionAdd';
import RoleAdd from '@/View/Permission/RoleAdd';


Vue.use(Router);

const router = new Router({
    routes: [
        {
            path: '/',
            component: Main,
            children: [
                {
                    path: '',
                    component: Layout,
                    children: [
                        {
                            path: '',
                            component: Dashboard
                        },
                        {
                            path: 'database',
                            component: Database
                        },
                        {
                            path: 'permission',
                            component: Permission
                        },
                        {
                            path: 'permission/:id',
                            component: PermissionAdd,
                            props: true
                        },
                        {
                            path: 'role/:id',
                            component: RoleAdd,
                            props: true
                        },
                        {
                            path: 'user',
                            component: Users
                        },
                        {
                            path: 'user/:id',
                            component: UserAdd,
                            props: true
                        },
                        {
                            path: 'license',
                            component: License
                        },
                        {
                            path: 'license/:id',
                            component: LicenseAdd,
                            props: true
                        },
                        {
                            path: 'userLicense/:id',
                            component: UserLicenseAdd,
                            props: true
                        }
                    ]
                },
                {
                    path: '/login',
                    component: Login,
                    meta: { noRequireAuthorization: true }
                }
            ]
        }
    ]
});

router.beforeEach((to, from, next) => {

    if (to.matched.some(record => record.meta.noRequireAuthorization)) {
        next();
    } else {
        if (to.path !== '/login') {
            AuthMiddleware.checkIsUserLogged()
                .then((returned)=>{
                    if(returned){
                        next();
                        setTimeout(()=>{
                            if(window.jsFrameworkAfterChangeRoute) window.jsFrameworkAfterChangeRoute();
                        },300);
                    }else{
                        next({
                            path: '/login',
                            query: {
                                redirect: to.fullPath ? to.fullPath : null,
                            }
                        });
                    }
                })
                .catch((erer)=>{
                    next({
                        path: '/login',
                        query: {
                            redirect: to.fullPath ? to.fullPath : null,
                        }
                    });
                });
        } else {
            next();
        }
    }
});

export default router;
