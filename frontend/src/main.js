import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'
import App from './App.vue'
import './style.css'

// Configure axios base URL
axios.defaults.baseURL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

const app = createApp(App)
app.use(createPinia())
app.mount('#app')
