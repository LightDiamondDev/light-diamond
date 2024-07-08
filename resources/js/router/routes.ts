import Home from '@/components/Home.vue'
import NotFound from '@/components/NotFound.vue'
import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'

declare module 'vue-router' {
    interface RouteMeta {
        title?: string
        requiresAuth?: boolean
        requiresModerator?: boolean
        requiresAdmin?: boolean
        defaultComponent?: Component
    }
}

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta:
            {
                title: 'Добро пожаловать'
            }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta:
            {
                title: 'Не найдено'
            }
    },
]

export default routes
