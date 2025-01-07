<script setup lang="ts">
import {useGlobalModalStore} from '@/stores/global-modal'
import AuthDialog from '@/components/auth/AuthDialog.vue'
import VerifyEmailDialog from '@/components/auth/VerifyEmailDialog.vue'

const globalModalStore = useGlobalModalStore()
</script>

<template>
    <AuthDialog v-model:visible="globalModalStore.isAuthModal"/>
    <VerifyEmailDialog v-model:visible="globalModalStore.isNotVerifiedEmailModal"/>
</template>
