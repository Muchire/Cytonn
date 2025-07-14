import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faSignOutAlt } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// Initialize auth state
import { useAuthStore } from './stores/auth.js'

const app = createApp(App)
const pinia = createPinia()

// Add Font Awesome icons
library.add(faSignOutAlt)
app.component('font-awesome-icon', FontAwesomeIcon)

app.use(pinia)
app.use(router)

const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')