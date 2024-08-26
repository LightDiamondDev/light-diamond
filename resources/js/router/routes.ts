import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'
import {GameEdition} from '@/types'

import Home from '@/components/Home.vue'
import Catalog from '@/components/catalog/Catalog.vue'
import CreatePost from '@/components/post/editor/CreatePost.vue'
import PostCategory from '@/components/post/PostCategory.vue'
import PostVersion from '@/components/post/editor/PostVersion.vue'
import Post from '@/components/post/Post.vue'
import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import ResetForm from '@/components/auth/ResetForm.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import Settings from '@/components/settings/Settings.vue'
import StudioMaterials from '@/components/studio/StudioMaterials.vue'
import StudioRequests from '@/components/studio/StudioRequests.vue'
import Studio from '@/components/studio/Studio.vue'
import NotFound from '@/components/NotFound.vue'
import VerifyEmail from '@/components/auth/VerifyEmail.vue'

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
        props: { edition: GameEdition.BEDROCK },
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
        }
    },
    {
        path: '/java/:category',
        component: Catalog,
        props: ({params}) => ({edition: GameEdition.BEDROCK, categorySlug: params.category as string}),
        meta: {
            title: 'Категория Java',
        }
    },
    {
        path: '/studio',
        name: 'studio',
        component: Studio,
        redirect: { name: 'studio.materials' },
        meta: {
            title: 'Контент-Студия',
            requiresAuth: true,
        },
        children:
            [
                {
                    path: 'materials',
                    name: 'studio.materials',
                    component: StudioMaterials,
                    meta: {
                        title: 'Контент-Студия — Материалы',
                    }
                },
                {
                    path: 'requests',
                    name: 'studio.requests',
                    component: StudioRequests,
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
            title: 'Создание Материала',
            requiresAuth: true
        }
    },
    {
        path: '/post-version/:id',
        name: 'post-version',
        props: ({params}) => ({id: Number.parseInt(params.id as string, 10) || 0}),
        component: PostVersion,
        meta: {
            title: 'Заявка на публикацию',
            requiresAuth: true
        }
    },
    {
        path: '/post/:slug/',
        name: 'post',
        props: true,
        component: Post,
    },
    {
        path: '/category/:slug',
        name: 'post-category',
        props: true,
        component: PostCategory,
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
