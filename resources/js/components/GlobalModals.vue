<script setup lang="ts">
import {useGlobalModalStore} from '@/stores/global-modal'
import Button from '@/components/elements/Button.vue'
import AuthDialog from '@/components/auth/AuthDialog.vue'

const globalModalStore = useGlobalModalStore()

let successNickname = false
let a = true
</script>

<template>

    <AuthDialog v-model:visible="globalModalStore.isAuth"/>

</template>

<style scoped/>
