import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { setupInterceptors } from './api/interceptors'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)
setupInterceptors(router)

app.mount('#app')
