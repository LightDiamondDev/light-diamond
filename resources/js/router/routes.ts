import usePreferenceManager from '@/preference-manager'

import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'
import {GameEdition} from '@/types'

import Home from '@/components/Home.vue'
import Catalog from '@/components/catalog/Catalog.vue'

import NewPostVersion from '@/components/post/editor/NewPostVersion.vue'

import Dashboard from '@/components/dashboard/Dashboard.vue'
import DashboardPostSubmissionsSection from '@/components/dashboard/DashboardPostSubmissionsSection.vue'
import DashboardUsersSection from '@/components/dashboard/DashboardUsersSection.vue'

import PostVersion from '@/components/post/editor/PostVersion.vue'
import Post from '@/components/post/Post.vue'

import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import Settings from '@/components/settings/Settings.vue'

import StudioPosts from '@/components/studio/StudioPosts.vue'
import StudioRequests from '@/components/studio/StudioRequests.vue'
import Studio from '@/components/studio/Studio.vue'

import NotFound from '@/components/NotFound.vue'

import ResetForm from '@/components/auth/ResetForm.vue'

import VerifyEmail from '@/components/auth/VerifyEmail.vue'
import useCategoryRegistry from '@/categoryRegistry'

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
        redirect: () => ({name: 'catalog-of', params: {edition: usePreferenceManager().getEdition().toLowerCase()}})
    },
    {
        path: '/:edition(bedrock|java)/:category?',
        name: 'catalog-of',
        component: Catalog,
        props: ({params}) => {
            const edition = GameEdition[(params.edition as string).toUpperCase() as keyof typeof GameEdition]
            return {
                edition: edition,
                category: params.category ? useCategoryRegistry().getBySlugAndEdition(params.category as string, edition) : undefined
            }
        },
        meta: {
            title: 'Каталог'
        }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        redirect: {name: 'dashboard.post-submissions'},
        meta: {
            title: 'Панель Управления',
            requiresAuth: true,
            requiresModerator: true
        },
        children: [
            {
                path: 'post-submissions',
                name: 'dashboard.post-submissions',
                component: DashboardPostSubmissionsSection,
                meta: {
                    title: 'Заявки на публикацию — Панель Управления',
                },
            },
            {
                path: 'users',
                name: 'dashboard.users',
                component: DashboardUsersSection,
                meta: {
                    title: 'Пользователи — Панель Управления',
                }
            }
        ]
    },
    {
        path: '/studio',
        name: 'studio',
        component: Studio,
        redirect: {name: 'studio.posts'},
        meta: {
            title: 'Контент-Студия',
            requiresAuth: true,
        },
        children: [
            {
                path: 'posts',
                name: 'studio.posts',
                component: StudioPosts,
                meta: {
                    title: 'Контент-Студия — Посты',
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
        component: NewPostVersion,
        meta: {
            title: 'Создание Поста',
            requiresAuth: true
        }
    },
    {
        path: '/update-post/:slug',
        name: 'update-post',
        props: ({params}) => ({slug: params.slug}),
        component: NewPostVersion,
        meta: {
            title: 'Обновление Поста',
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
            requiresAuth: true,
        }
    },
    {
        path: '/post/:slug/',
        name: 'post',
        props: true,
        component: Post,
        meta: {
            title: 'Пост',
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
        path: '/settings',
        name: 'settings',
        component: Settings,
        redirect: {name: 'settings.profile'},
        meta: {
            title: 'Настройки',
            requiresAuth: true,
        },
        children: [
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
