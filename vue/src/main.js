import { createApp } from 'vue'
// import './style.css'
import store from './store'
import './index.css'
import App from './App.vue'

createApp(App)
    .use(store)
    .mount('#app')
