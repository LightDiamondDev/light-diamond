import useCategoryRegistry from '@/categoryRegistry'
import usePreferenceManager from '@/preference-manager'

import type {RouteRecordRaw} from 'vue-router'
import type {Component} from 'vue'
import {GameEdition, MaterialSubmissionStatus} from '@/types'

import AboutUs from '@/components/elements/AboutUs.vue'
import Catalog from '@/components/catalog/Catalog.vue'
import Home from '@/components/Home.vue'

import NewMaterialSubmission from '@/components/material/editor/NewMaterialSubmission.vue'

import Dashboard from '@/components/dashboard/Dashboard.vue'
import DashboardMaterialSubmissions from '@/components/dashboard/DashboardMaterialSubmissions.vue'
import DashboardUsers from '@/components/dashboard/DashboardUsers.vue'

import MaterialSubmission from '@/components/material/editor/MaterialSubmission.vue'
import Material from '@/components/material/Material.vue'

import PrivacyPolicy from '@/components/elements/PrivacyPolicy.vue'

import Profile from '@/components/user/profile/Profile.vue'
import ProfileComments from '@/components/user/profile/ProfileComments.vue'
import ProfileFavouriteMaterials from '@/components/user/profile/ProfileFavouriteMaterials.vue'
import ProfileMaterials from '@/components/user/profile/ProfileMaterials.vue'

import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import Settings from '@/components/settings/Settings.vue'

import StudioMaterials from '@/components/studio/StudioMaterials.vue'
import StudioSubmissions from '@/components/studio/StudioSubmissions.vue'
import Studio from '@/components/studio/Studio.vue'

import TermsOfUse from '@/components/elements/TermsOfUse.vue'

import NotFound from '@/components/NotFound.vue'

import ResetForm from '@/components/auth/ResetForm.vue'

import VerifyEmail from '@/components/auth/VerifyEmail.vue'
import {changeTitle} from '@/helpers'

declare module 'vue-router' {
    interface RouteMeta {
        title?: string
        requiresAuth?: boolean
        requiresModerator?: boolean
        requiresAdmin?: boolean
        defaultComponent?: Component
    }
}

function getEditionAndCategoryFromParams(params: object) {
    const edition = params.edition ? GameEdition[(params.edition as string).toUpperCase() as keyof typeof GameEdition] : null
    const category = useCategoryRegistry().getBySlugAndEdition(params.category as string, edition)

    return {edition: edition, category: category}
}

const categorySlugs = useCategoryRegistry().getAll().map((category) => category.slug).join('|')

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
        path: `/:category(${categorySlugs})`,
        redirect: (to) => ({
            name: 'catalog-of',
            params: {edition: usePreferenceManager().getEdition().toLowerCase(), category: to.params.category},
        }),
    },
    {
        path: `/:edition(bedrock|java)/:category(${categorySlugs})?`,
        name: 'catalog-of',
        component: Catalog,
        props: ({params}) => getEditionAndCategoryFromParams(params),
        beforeEnter: (to, from, next) => {
            const {category} = getEditionAndCategoryFromParams(to.params)
            if (!category && to.params.category) {
                next({
                    name: 'catalog-of',
                    params: {edition: to.params.edition},
                })
            } else {
                next()
            }
        },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        redirect: {name: 'dashboard.material-submissions'},
        meta: {
            title: 'Панель Управления',
            requiresAuth: true,
            requiresModerator: true
        },
        children: [
            {
                path: 'material-submissions',
                name: 'dashboard.material-submissions',
                component: DashboardMaterialSubmissions,
                meta: {
                    title: 'Ожидающие заявки на публикацию — Панель Управления',
                },
                props: () => ({status: MaterialSubmissionStatus.PENDING})
            },
            {
                path: 'material-submissions/accepted',
                name: 'dashboard.material-submissions.accepted',
                component: DashboardMaterialSubmissions,
                meta: {
                    title: 'Принятые заявки на публикацию — Панель Управления',
                },
                props: () => ({status: MaterialSubmissionStatus.ACCEPTED})
            },
            {
                path: 'material-submissions/rejected',
                name: 'dashboard.material-submissions.rejected',
                component: DashboardMaterialSubmissions,
                meta: {
                    title: 'Отклонённые заявки на публикацию — Панель Управления',
                },
                props: () => ({status: MaterialSubmissionStatus.REJECTED})
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
        redirect: {name: 'studio.materials'},
        meta: {
            title: 'Контент-Студия',
            requiresAuth: true
        },
        children: [
            {
                path: 'materials',
                name: 'studio.materials',
                component: StudioMaterials,
                meta: {
                    title: 'Материалы — Контент-Студия',
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
                props: () => ({status: MaterialSubmissionStatus.DRAFT})
            },
            {
                path: 'submissions/pending',
                name: 'studio.submissions.pending',
                component: StudioSubmissions,
                meta: {
                    title: 'Ожидающие заявки — Контент-Студия',
                },
                props: () => ({status: MaterialSubmissionStatus.PENDING})
            },
            {
                path: 'submissions/accepted',
                name: 'studio.submissions.accepted',
                component: StudioSubmissions,
                meta: {
                    title: 'Принятые заявки — Контент-Студия',
                },
                props: () => ({status: MaterialSubmissionStatus.ACCEPTED})
            },
            {
                path: 'submissions/rejected',
                name: 'studio.submissions.rejected',
                component: StudioSubmissions,
                meta: {
                    title: 'Отклонённые заявки — Контент-Студия',
                },
                props: () => ({status: MaterialSubmissionStatus.REJECTED})
            }
        ]
    },
    {
        path: '/create-material',
        name: 'create-material',
        component: NewMaterialSubmission,
        meta: {
            title: 'Создание Материала',
            requiresAuth: true
        }
    },
    {
        path: `/:edition(bedrock|java)?/:category(${categorySlugs})/:slug/update`,
        name: 'update-material',
        props: ({params}) => ({
            ...getEditionAndCategoryFromParams(params),
            slug: params.slug,
        }),
        beforeEnter: (to) => {
            const {category} = getEditionAndCategoryFromParams(to.params)
            if (!category) {
                to.matched[0].components!.default = NotFound
            }
        },
        component: NewMaterialSubmission,
        meta: {
            title: 'Обновление Материала',
            requiresAuth: true
        }
    },
    {
        path: '/material-submissions/:id',
        name: 'material-submission',
        props: ({params}) => ({id: Number.parseInt(params.id as string, 10) || 0}),
        component: MaterialSubmission,
        meta: {
            title: 'Заявка на публикацию',
            requiresAuth: true,
        }
    },
    {
        path: `/:edition(bedrock|java)?/:category(${categorySlugs})/:slug/`,
        name: 'material',
        props: ({params}) => ({
            ...getEditionAndCategoryFromParams(params),
            slug: params.slug,
        }),
        beforeEnter: (to) => {
            const {category} = getEditionAndCategoryFromParams(to.params)
            if (!category) {
                to.matched[0].components!.default = NotFound
            }
        },
        component: Material,
        meta: {
            title: 'Материал',
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
        path: '/users/:username',
        name: 'profile',
        component: Profile,
        props: ({params}) => ({username: params.username}),
        redirect: {name: 'profile.materials'},
        meta: {
            title: 'Профиль'
        },
        children: [
            {
                path: '',
                alias: ['materials'],
                name: 'profile.materials',
                component: ProfileMaterials,
                meta: {
                    title: 'Профиль',
                }
            },
            {
                path: 'favourite-materials',
                name: 'profile.favourite-materials',
                component: ProfileFavouriteMaterials,
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
