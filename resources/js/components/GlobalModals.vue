<script setup lang="ts">
    import {useGlobalModalStore} from '@/stores/global-modal'
    import {computed, onMounted, onUnmounted, ref, watch} from 'vue'
    import {storeToRefs} from 'pinia'
    import {getCssVariableValue, lockGlobalScroll, remToPixels, unlockGlobalScroll} from '@/helpers'
    import tailwindConfig from '../../../tailwind.config'

    enum AuthFormType {
        LOGIN,
        REGISTER,
        FORGOT_PASSWORD
    }

    const globalModalStore = useGlobalModalStore()
    const {isAuth: isAuthModal} = storeToRefs(globalModalStore)
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

    //watch(isAuthModal, (showAuthModal: boolean) => {
    //    if (!showAuthModal) {
    //        authFormType.value = AuthFormType.LOGIN
    //    }
    //})
    isAuthModal.value = false
</script>

<template>
    <div v-if="globalModalStore.isAuth" class="auth-modal-window flex">
        <div class="interface">
            <form action="" class="register">
                <div>yutyytuyt</div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.auth-modal-window {
    background-color: rgba(0, 0, 0, .5);
    position: fixed;
    height: 100%;
    width: 100%;
    z-index: 1;
}
</style>
