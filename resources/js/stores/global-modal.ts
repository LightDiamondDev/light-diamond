import {defineStore} from 'pinia'

export const useGlobalModalStore = defineStore('global-modal', {
    state: () => ({
        authModal: false,
        notVerifiedEmailModal: false,
        captchaModal: false,
        captchaModalAction: (_token) => {},
    })
})
