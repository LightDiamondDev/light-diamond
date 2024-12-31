import useCategoryRegistry from '@/categoryRegistry'
import usePreferenceManager from '@/preference-manager'

import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'
import {GameEdition, PostVersionStatus} from '@/types'

import AboutUs from '@/components/elements/AboutUs.vue'
import Catalog from '@/components/catalog/Catalog.vue'
import Home from '@/components/Home.vue'

import NewPostVersion from '@/components/post/editor/NewPostVersion.vue'

import Dashboard from '@/components/dashboard/Dashboard.vue'
import DashboardPostSubmissions from '@/components/dashboard/DashboardPostSubmissions.vue'
import DashboardUsers from '@/components/dashboard/DashboardUsers.vue'

import PostVersion from '@/components/post/editor/PostVersion.vue'
import Post from '@/components/post/Post.vue'

import PrivacyPolicy from '@/components/elements/PrivacyPolicy.vue'

import Profile from '@/components/user/profile/Profile.vue'
import ProfileComments from '@/components/user/profile/ProfileComments.vue'
import ProfileFavouritePosts from '@/components/user/profile/ProfileFavouritePosts.vue'
import ProfilePosts from '@/components/user/profile/ProfilePosts.vue'

import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import Settings from '@/components/settings/Settings.vue'

import StudioPosts from '@/components/studio/StudioPosts.vue'
import StudioSubmissions from '@/components/studio/StudioSubmissions.vue'
import Studio from '@/components/studio/Studio.vue'

import TermsOfUse from '@/components/elements/TermsOfUse.vue'

import NotFound from '@/components/NotFound.vue'

import ResetForm from '@/components/auth/ResetForm.vue'

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
        path: '/about-us/',
        name: 'about-us',
        component: AboutUs,
        meta: {
            title: 'О Нас',
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
                component: DashboardPostSubmissions,
                meta: {
                    title: 'Ожидающие заявки на публикацию — Панель Управления',
                },
                props: () => ({ status: PostVersionStatus.PENDING })
            },
            {
                path: 'post-submissions/accepted',
                name: 'dashboard.post-submissions.accepted',
                component: DashboardPostSubmissions,
                meta: {
                    title: 'Принятые заявки на публикацию — Панель Управления',
                },
                props: () => ({ status: PostVersionStatus.ACCEPTED })
            },
            {
                path: 'post-submissions/rejected',
                name: 'dashboard.post-submissions.rejected',
                component: DashboardPostSubmissions,
                meta: {
                    title: 'Отклонённые заявки на публикацию — Панель Управления',
                },
                props: () => ({ status: PostVersionStatus.REJECTED })
            },
            {
                path: 'users',
                name: 'dashboard.users',
                component: DashboardUsers,
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
            requiresAuth: true
        },
        children: [
            {
                path: 'posts',
                name: 'studio.posts',
                component: StudioPosts,
                meta: {
                    title: 'Посты — Контент-Студия',
                }
            },
            {
                path: 'submissions',
                name: 'studio.submissions',
                redirect: {name: 'studio.submissions'},
                meta: {
                    title: 'Черновики заявок — Контент-Студия',
                }
            },
            {
                path: 'submissions/drafts',
                name: 'studio.submissions.drafts',
                component: StudioSubmissions,
                meta: {
                    title: 'Черновики заявок — Контент-Студия',
                },
                props: () => ({ status: PostVersionStatus.DRAFT})
            },
            {
                path: 'submissions/pending',
                name: 'studio.submissions.pending',
                component: StudioSubmissions,
                meta: {
                    title: 'Ожидающие заявки — Контент-Студия',
                },
                props: () => ({ status: PostVersionStatus.PENDING})
            },
            {
                path: 'submissions/accepted',
                name: 'studio.submissions.accepted',
                component: StudioSubmissions,
                meta: {
                    title: 'Принятые заявки — Контент-Студия',
                },
                props: () => ({ status: PostVersionStatus.ACCEPTED})
            },
            {
                path: 'submissions/rejected',
                name: 'studio.submissions.rejected',
                component: StudioSubmissions,
                meta: {
                    title: 'Отклонённые заявки — Контент-Студия',
                },
                props: () => ({ status: PostVersionStatus.REJECTED})
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
        path: '/privacy-policy/',
        name: 'privacy-policy',
        component: PrivacyPolicy,
        meta: {
            title: 'Политика Конфиденциальности',
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
        }
    },
    {
        path: '/user/:username',
        name: 'profile',
        component: Profile,
        props: ({params}) => ({username: params.username}),
        redirect: {name: 'profile.posts'},
        meta: {
            title: 'Профиль'
        },
        children: [
            {
                path: '',
                alias: ['posts'],
                name: 'profile.posts',
                component: ProfilePosts,
                meta: {
                    title: 'Профиль',
                }
            },
            {
                path: 'favourite-posts',
                name: 'profile.favourite-posts',
                component: ProfileFavouritePosts,
                meta: {
                    title: 'Профиль — Избранное',
                }
            },
            {
                path: 'comments',
                name: 'profile.comments',
                component: ProfileComments,
                meta: {
                    title: 'Профиль — Комментарии',
                }
            }
        ]
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
        path: '/terms-of-use/',
        name: 'terms-of-use',
        component: TermsOfUse,
        meta: {
            title: 'Правила Использования',
        }
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
