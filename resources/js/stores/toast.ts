import {defineStore} from 'pinia'

interface Toast {
    id: bigint,
    message: string,
    duration: number,
    type: 'success'|'error'|'warning'|'info'
}

interface ToastStore {
    currentId: bigint,
    toasts: Toast[]
}

export const useToastStore = defineStore('toast', {
    state: (): ToastStore => ({
        currentId: 0,
        toasts: []
    }),
    actions: {
        add(type: string, message: string, duration: number = 5000) {
            this.toasts.push({
                id: this.currentId,
                message: message,
                duration: duration,
                type: type
            })
            this.currentId++
        },

        success(message: string, duration: number = 5000) {
            this.add('success', message, duration)
        },

        error(message: string, duration: number = 5000) {
            this.add('error', message, duration)
        },

        warning(message: string, duration: number = 5000) {
            this.add('warning', message, duration)
        },

        info(message: string, duration: number = 5000) {
            this.add('info', message, duration)
        }
    }
})
