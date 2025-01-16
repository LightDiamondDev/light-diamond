<script setup lang="ts">
import {useGlobalModalStore} from '@/stores/global-modal'
import AuthDialog from '@/components/auth/AuthDialog.vue'
import NotVerifiedEmailDialog from '@/components/auth/NotVerifiedEmailDialog.vue'
import CookieNotice from '@/components/elements/CookieNotice.vue'
import CaptchaDialog from '@/components/elements/CaptchaDialog.vue'

const globalModalStore = useGlobalModalStore()
</script>

<template>
    <AuthDialog v-model:visible="globalModalStore.authModal"/>
    <NotVerifiedEmailDialog v-model:visible="globalModalStore.notVerifiedEmailModal"/>
    <CookieNotice/>
<!--    <CaptchaDialog v-model:visible="globalModalStore.isCaptcha"/>-->
</template>
