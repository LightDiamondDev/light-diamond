<script setup lang="ts">
    import {useRouter, useRoute, type RouteLocation} from 'vue-router'
    import {usePostCategoryStore} from '@/stores/postCategory'
    import {useToastStore} from '@/stores/toast'
    import {useAuthStore} from '@/stores/auth'
    import {watch} from 'vue'

    import GlobalModals from '@/components/GlobalModals.vue'
    import Toaster from '@/components/elements/Toaster.vue'
    import Header from '@/components/Header.vue'
    import Footer from '@/components/Footer.vue'

    usePostCategoryStore().load()

    const toastStore = useToastStore()
    const authStore = useAuthStore()
    const router = useRouter()
    const route = useRoute()

    watch(route, (to: RouteLocation) => {
        if (to.query.authorized) {
            toastStore.success(`Вы успешно вошли!`, 'Пидоры', 18000000)
            const {authorized, ...newQuery} = to.query
            router.replace({path: to.path, query: newQuery})
        }
        else if (to.query.registered) {
            toastStore.success(`Поздравляем! Вы успешно зарегистрировались!`)
            toastStore.info(
                `Мы отправили Вам письмо со ссылкой для подтверждения Вашей электронной почты!`,
                'Подтверждение E-Mail',
                15000,
                'icon-letter'
            )
            const {registered, ...newQuery} = to.query
            router.replace({path: to.path, query: newQuery})
        }
    })

</script>

<template>
    <GlobalModals/>
    <Toaster/>
    <div class="surface-ground ld-secondary-background ld-fixed-background flex flex-col items-center w-full">
        <Header/>
        <main class="flex flex-col items-center w-full">
            <RouterView></RouterView>
        </main>
        <Footer/>
    </div>
</template>

<style/>
