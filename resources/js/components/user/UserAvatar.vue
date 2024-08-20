<script setup lang="ts">
import type {PropType} from 'vue'
import type {User} from '@/types'

defineProps({
    user: {
        type: Object as PropType<User>,
        required: true,
    }
})
</script>

<template>
    <span class="icon icon-outline flex justify-center items-center icon-border h-[48px] w-[48px]">
        <img alt="" src="/images/users/content/funny-girl.png" class="h-[32px]">
    </span>
</template>

<style scoped>

</style>
