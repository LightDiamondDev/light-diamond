import {createRouter, createWebHistory} from 'vue-router'
import {type Component} from 'vue'
import routes from '@/router/routes'
import {useAuthStore} from '@/stores/auth'
import {changeTitle, getHeaderHeight} from '@/helpers'
import AuthRequired from '@/components/auth/AuthRequired.vue'
import NoPermission from '@/components/NoPermission.vue'
import VerifiedEmailRequired from '@/components/auth/VerifiedEmailRequired.vue'

let hashObserver: ResizeObserver | undefined = undefined
let hashObserverDisconnectTimeout: number | undefined = undefined

function disconnectHashObserver() {
    if (hashObserver) {
        hashObserver.disconnect()
        clearTimeout(hashObserverDisconnectTimeout)
        hashObserver = undefined
        hashObserverDisconnectTimeout = undefined
    }
}

function getHashScrollTopOffset() {
    return getHeaderHeight() + 16
}

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
    scrollBehavior(to, from, savedPosition) {
        return new Promise((resolve) => {
            if (to.hash) {
                disconnectHashObserver()

                if (to.path === from.path) {
                    resolve({el: to.hash, top: getHashScrollTopOffset(), behavior: 'smooth'})
                } else {
                    let resolveTimeout: number | undefined = undefined

                    hashObserver = new ResizeObserver(entries => {
                        if (resolveTimeout) {
                            clearTimeout(resolveTimeout)
                        }

                        const element = entries[0].target.querySelector(to.hash)
                        if (element) {
                            resolveTimeout = window.setTimeout(() => {
                                disconnectHashObserver()
                                resolve({el: element, top: getHashScrollTopOffset(), behavior: 'smooth'})
                            }, 500)
                        }
                    })
                    hashObserver.observe(document.body)

                    hashObserverDisconnectTimeout = window.setTimeout(disconnectHashObserver, 10000)
                }
            } else if (savedPosition) {
                savedPosition.behavior = 'smooth'

                const resizeObserver = new ResizeObserver(entries => {
                    if (entries[0].target.clientHeight >= savedPosition.top + screen.height) {
                        resolve(savedPosition)
                        resizeObserver.disconnect()
                        clearTimeout(timeoutId)
                    }
                })

                resizeObserver.observe(document.body)
                const timeoutId = setTimeout(() => {
                    resolve(savedPosition)
                    resizeObserver.disconnect()
                }, 1000)
            } else {
                resolve({top: 0})
            }
        })
    }
})

router.beforeEach((to, _from, next) => {
    const authStore= useAuthStore()

    function displayComponent(component?: Component) {
        to.matched[0].components!.default = component ?? to.matched[0].meta.defaultComponent!
        next()
    }

    function processRouteNavigation() {
        if (
            to.matched.some(record => record.meta.requiresAuth) &&
            !authStore.isAuthenticated
        ) {
            displayComponent(AuthRequired)
            return
        }

        if (
            to.matched.some(record => record.meta.requiresEmailVerified)
        ) {
            displayComponent(VerifiedEmailRequired)
            return
        }

        if (
            to.matched.some(record => record.meta.requiresModerator) &&
            !authStore.isModerator
        ) {
            displayComponent(NoPermission)
            return
        }

        if (
            to.matched.some(record => record.meta.requiresAdmin) &&
            !authStore.isAdmin
        ) {
            displayComponent(NoPermission)
            return
        }

        displayComponent()
    }

    if (!to.matched[0].meta.defaultComponent) {
        to.matched[0].meta.defaultComponent = to.matched[0].components!.default
    }

    if (!authStore.isFetched) {
        authStore.fetchUser().then(processRouteNavigation)
    } else {
        processRouteNavigation()
    }
})

router.afterEach((to) => {
    if (to.meta.title) {
        changeTitle(to.meta.title)
    }
})

export default router
