<script setup lang="ts">
import type {PropType} from 'vue'
import type {User} from '@/types'

const props = defineProps({
    user: {
        type: Object as PropType<User>
    }
})
</script>

<template>
    <Component :is="user ? 'RouterLink' : 'span'" :to="{ name: 'profile', params: {username: user?.username} }">
        <slot/>
    </Component>
</template>

<style scoped/>
