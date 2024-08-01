import Home from '@/components/Home.vue'
import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'
import Processing from '@/components/elements/Processing.vue'
import NoPermission from '@/components/NoPermission.vue'
import AuthRequired from '@/components/auth/AuthRequired.vue'
import VerifyEmail from '@/components/auth/VerifyEmail.vue'
import ResetForm from '@/components/auth/ResetForm.vue'
import NotFound from '@/components/NotFound.vue'

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
        meta: {
            title: 'Добро пожаловать'
        }
    },
    {
        path: '/email/verify/:id/:hash',
        name: 'verify-email',
        component: VerifyEmail,
        meta: {
            title: 'Подтверждение E-mail',
        },
    },
    {
        path: '/password/reset',
        name: 'password-reset',
        component: ResetForm,
        meta: {
            title: 'Сброс пароля',
        },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta: {
            title: 'Не найдено'
        }
    }
]

export default routes
