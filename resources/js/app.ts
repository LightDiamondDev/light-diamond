import '@/bootstrap'
import {createApp} from "vue"
import App from "@/components/App.vue"
import router from "@/router"
import {createPinia} from "pinia"
import { tooltipDirective } from '@/tooltip'

const app = createApp(App)

app.directive('tooltip', tooltipDirective)
app.use(router)
app.use(createPinia())

app.mount('#app')
