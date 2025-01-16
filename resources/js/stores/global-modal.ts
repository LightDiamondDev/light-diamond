import {defineStore} from 'pinia'

export const useGlobalModalStore = defineStore('global-modal', {
    state: () => ({
        isCookiesModal: false,
        isAuthModal: false,
        isNotVerifiedEmailModal: false,
    })
})
