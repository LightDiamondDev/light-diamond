import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'

import Home from '@/components/Home.vue'
import Catalog from '@/components/catalog/Catalog.vue'
import ContentStudio from '@/components/studio/Studio.vue'
import ContentStudioMaterials from '@/components/studio/StudioMaterials.vue'
import ContentStudioRequests from '@/components/studio/StudioRequests.vue'
import CreatePost from '@/components/post/editor/CreatePost.vue'

// import Processing from '@/components/elements/ProcessingMovingItems.vue'
// import NoPermission from '@/components/NoPermission.vue'
// import AuthRequired from '@/components/auth/AuthRequired.vue'

import VerifyEmail from '@/components/auth/VerifyEmail.vue'
import ResetForm from '@/components/auth/ResetForm.vue'
import Settings from '@/components/settings/Settings.vue'
import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import NotFound from '@/components/NotFound.vue'
import {GameEdition} from '@/types'

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
        path: '/bedrock',
        name: 'catalog.bedrock',
        component: Catalog,
        props: {edition: GameEdition.BEDROCK},
        meta: {
            title: 'Каталог Bedrock',
        },
    },
    {
        path: '/bedrock/:category',
        component: Catalog,
        props: ({params}) => ({edition: GameEdition.BEDROCK, categorySlug: params.category as string}),
        meta: {
            title: 'Категория Bedrock',
        },
    },
    {
        path: '/java',
        name: 'catalog.java',
        component: Catalog,
        props: {edition: GameEdition.JAVA},
        meta: {
            title: 'Каталог Java',
        },
    },
    {
        path: '/java/:category',
        component: Catalog,
        props: ({params}) => ({edition: GameEdition.BEDROCK, categorySlug: params.category as string}),
        meta: {
            title: 'Категория Java',
        },
    },
    {
        path: '/content-studio',
        name: 'content-studio',
        component: ContentStudio,
        redirect: { name: 'content-studio.materials' },
        meta: {
            title: 'Контент-Студия',
            requiresAuth: true,
        },
        children:
            [
                {
                    path: 'materials',
                    name: 'content-studio.materials',
                    component: ContentStudioMaterials,
                    meta: {
                        title: 'Контент-Студия — Материалы',
                    }
                },
                {
                    path: 'requests',
                    name: 'content-studio.requests',
                    component: ContentStudioRequests,
                    meta: {
                        title: 'Контент-Студия — Заявки на публикацию',
                    }
                }
            ]
    },
    {
        path: '/create-post',
        name: 'create-post',
        component: CreatePost,
        meta: {
            title: 'Редактор Материала',
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
        redirect: { name: 'settings.profile' },
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
