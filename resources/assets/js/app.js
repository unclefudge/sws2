
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

// Vue 2 method
//window.Vue = require('vue')

// Vue 3
import { createApp } from 'vue';

// My App
import App from './components/app.vue'

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));


// Vue 2 method
//const app = new Vue({
//    el: '#vueApp'
//});

// Vue 3
createApp(App).mount('#vueApp');
