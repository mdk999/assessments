import Vue from 'vue';
import _ from 'lodash';
import App from './App.vue';
import axios from 'axios';
import VueAxios from 'vue-axios';

Vue.use(VueAxios, axios, _);


new Vue({
    render: h => h(App),
}).$mount('#app');