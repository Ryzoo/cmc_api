window.devMode = true;

import 'promise-polyfill/src/polyfill';
import Vue from 'vue';
import router from './router.js';
require("../static/styles/global.scss");
import Notify from './components/additionalClass/Notify';
import AuthMiddleware from './components/additionalClass/AuthMiddleware';
import Validator from './components/additionalClass/Validator';
import Sortable from "../static/lib/sortable-0.8.0/js/sortable.min.js";
import wow from "../static/lib/wow.min.js";

import SortableJs from 'sortablejs';
window.SortableJs = SortableJs;

window.wow = new wow();

window.Sortable = Sortable;
window.axios = require('axios');
window.bowser = require('bowser');
window.AuthMiddleware = new AuthMiddleware();
window.Validator = Validator;

require ("../static/lib/sortable-0.8.0/css/sortable-theme-light.css");
require ("../static/lib/animate.css");


window.axios.defaults.baseURL = window.devMode ? 'http://cmcapi.pl' : 'https://api.centrumklubu.pl';
window.axios.defaults.headers.post['Content-Type'] = 'application/json';
window.axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    if(error.response.data && error.response.data.error){
        notify.createNotifier(error.response.data.error,"danger",5000);
        return Promise.reject(error);
    }
});

Vue.mixin({
    methods: {
        go(url) {
            this.$router.push(url);
        },
        showModal(name) {
            $(name).showModal()
        },
        hideModal(name) {
            $(name).hideModal()
        }
    }
});

window.notify = new Notify();               // show local notfiy

Vue.config.productionTip = true;

Vue.component("loader", require("./components/Loader.vue").default);

new Vue({
    el: '#app',
    router
});
