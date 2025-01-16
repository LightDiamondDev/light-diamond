import {defineStore} from 'pinia'

export const useGlobalModalStore = defineStore('global-modal', {
    state: () => ({
        authModal: false,
        notVerifiedEmailModal: false,
    })
})
