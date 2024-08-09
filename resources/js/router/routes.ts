import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'

import Home from '@/components/Home.vue'
import Catalog from '@/components/Catalog.vue'

import Processing from '@/components/elements/ProcessingMovingItems.vue'
import NoPermission from '@/components/NoPermission.vue'
import AuthRequired from '@/components/auth/AuthRequired.vue'
import VerifyEmail from '@/components/auth/VerifyEmail.vue'
import ResetForm from '@/components/auth/ResetForm.vue'
import Settings from '@/components/settings/Settings.vue'
import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
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
        path: '/catalog',
        name: 'catalog',
        component: Catalog,
        meta: {
            title: 'Каталог',
        },
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
        path: '/settings',
        name: 'settings',
        component: Settings,
        redirect: {name: 'settings.profile'},
        meta: {
            title: 'Настройки',
            requiresAuth: true,
        },
        children:
        [
            {
                path: 'profile',
                name: 'settings.profile',
                component: ProfileSettings,
                meta: {
                    title: 'Настройки профиля',
                }
            },
            {
                path: 'security',
                name: 'settings.security',
                component: SecuritySettings,
                meta: {
                    title: 'Настройки безопасности',
                }
            }
        ]
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
