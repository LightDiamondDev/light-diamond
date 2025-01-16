<script setup lang="ts">
import {useGlobalModalStore} from '@/stores/global-modal'
import AuthDialog from '@/components/auth/AuthDialog.vue'
import VerifyEmailDialog from '@/components/auth/VerifyEmailDialog.vue'
import CookieNotice from '@/components/elements/CookieNotice.vue'

const globalModalStore = useGlobalModalStore()
</script>

<template>
    <AuthDialog v-model:visible="globalModalStore.isAuthModal"/>
    <VerifyEmailDialog v-model:visible="globalModalStore.isNotVerifiedEmailModal"/>
    <CookieNotice/>
</template>
