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
        <span class="notifications-counter flex justify-center items-center">15</span>
    </span>
</template>

<style scoped>
.notifications-counter,
.notifications-counter {
    background-color: rgb(210, 10, 30);
    color: var(--primary-text-color);
    margin: 0 0 32px 32px;
    position: absolute;
    text-align: center;
    padding-left: 2px;
    font-size: .5rem;
    height: 16px;
    width: 16px;
}
</style>
