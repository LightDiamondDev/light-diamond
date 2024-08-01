import {defineStore} from 'pinia'

export enum ToastType {
    ERROR,
    INFO,
    SUCCESS,
    WARNING
}

export interface Toast {
    id: bigint
    type: ToastType
    message: string
    title: string
    duration: number
    icon?: string
}

interface ToastStore {
    currentId: bigint
    toasts: Toast[]
}

export const useToastStore = defineStore('toast', {
    state: (): ToastStore => ({
        currentId: 0n,
        toasts: []
    }),
    actions: {
        remove(id: bigint) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id)
        },

        add(
            type: ToastType,
            message: string,
            title: string,
            duration: number = 8000,
            icon?: string
        ) {
            this.toasts.push({
                id: this.currentId,
                type: type,
                message: message,
                title: title,
                duration: duration,
                icon: icon
            })
            this.currentId++
        },

        error(message: string, title: string = 'Ошибка!', duration: number = 8000, icon?: string) {
            this.add(ToastType.ERROR, message, title, duration, icon)
        },

        info(message: string, title: string = 'Уведомление', duration: number = 8000, icon?: string) {
            this.add(ToastType.INFO, message, title, duration, icon)
        },

        success(message: string, title: string = 'Успех!', duration: number = 8000, icon?: string) {
            this.add(ToastType.SUCCESS, message, title, duration, icon)
        },

        warning(message: string, title: string = 'Внимание!', duration: number = 8000, icon?: string) {
            this.add(ToastType.WARNING, message, title, duration, icon)
        }
    }
})
