import Vue from 'vue'
import axios from 'axios'

Vue.prototype.$axios = axios


export const send = axios.create({
    baseURL: 'https://esy.fusiontechph.com/esyadmin',
    // baseURL:'http://localhost/esyadmin',
    // timeout: 10000,
    headers: {
        Authorization: sessionStorage.esyAdmin || ''
    }
});

Vue.prototype.$axios = send;

