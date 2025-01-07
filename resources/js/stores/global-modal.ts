import {defineStore} from 'pinia'

export const useGlobalModalStore = defineStore('global-modal', {
    state: () => ({
        isAuthModal: false,
        isNotVerifiedEmailModal: false,
    })
})
