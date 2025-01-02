<script setup lang="ts">
import {useGlobalModalStore} from '@/stores/global-modal'
import AuthDialog from '@/components/auth/AuthDialog.vue'
import CaptchaDialog from '@/components/elements/CaptchaDialog.vue'

const globalModalStore = useGlobalModalStore()
</script>

<template>
    <AuthDialog v-model:visible="globalModalStore.isAuth"/>
<!--    <CaptchaDialog v-model:visible="globalModalStore.isCaptcha"/>-->
</template>

<style scoped/>
