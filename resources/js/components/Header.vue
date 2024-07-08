<script setup lang='ts'>
import {useAuthStore} from '@/stores/auth'
import useThemeManager from '@/theme-manager'
import {useRouter} from 'vue-router'
import {useModalStore} from '@/stores/modal'
import {usePostCategoryStore} from '@/stores/postCategory'

const router = useRouter()
const authStore = useAuthStore()
const categoryStore = usePostCategoryStore()
const modalStore = useModalStore()
const themeManager = useThemeManager()
</script>

<template>

</template>

<style scoped>

</style>
