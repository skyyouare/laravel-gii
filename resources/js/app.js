/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

//element ui
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
Vue.use(ElementUI);

//路由
import App from './App.vue'; //添加的内容

//路由
require('./router.js');
import VueRouter from 'vue-router'
Vue.use(VueRouter);

const router =  new VueRouter({
    mode: 'history', //把Router的mode修改为history模式,VueRouter默认的模式为HASH模式
    saveScrollPosition: true,
    routes: routes
})

//axios重写
require('./ajax.js');//添加的内容

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('main-layout', require('./layouts/main.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const APP = new Vue({
    el: '#app',
    router,  //添加的内容
    render:h => h(App)//添加的内容
});



// const app = new Vue({
//     el: '#app',
//     delimiters: ['${', '}'], //${abc}
//     data: function () {
//         return {
//             task_title: ['待处理', '待知晓'],
//             taskActive: '待处理',
//             dynamicList: ['公司动态', '趣WAY'],
//             dynamicActive: '公司动态',
//         };
//     },
//     methods: {
//         taskClick: function (item) {
//             this.taskActive = item
//         },
//         dynamicClick: function (item) {
//             this.dynamicActive = item
//         },
//     },
//     created: function () {
//     }
// });
