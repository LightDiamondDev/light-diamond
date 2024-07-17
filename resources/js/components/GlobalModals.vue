<script setup lang="ts">

import {useModalStore} from '@/stores/modal'
import {computed, onMounted, onUnmounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import tailwindConfig from '../../../tailwind.config'
import {getCssVariableValue, remToPixels} from '@/helpers'

enum AuthFormType {
    LOGIN,
    REGISTER,
    FORGOT_PASSWORD
}

const modalStore = useModalStore()
const {auth: isAuthModal} = storeToRefs(modalStore)
const authFormType = ref(AuthFormType.LOGIN)

const authDialogTitle = computed(() => {
    switch (authFormType.value) {
        case AuthFormType.LOGIN:
            return 'Вход'
        case AuthFormType.REGISTER:
            return 'Регистрация'
        case AuthFormType.FORGOT_PASSWORD:
            return 'Сброс пароля'
    }
})

watch(isAuthModal, (showAuthModal: boolean) => {
    if (!showAuthModal) {
        authFormType.value = AuthFormType.LOGIN
    }
})

</script>

<template>

</template>

<style scoped>

</style>
