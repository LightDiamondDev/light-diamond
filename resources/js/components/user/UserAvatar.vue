<script setup lang="ts">
import type {PropType} from 'vue'
import type {User} from '@/types'

defineProps({
    borderClassList: String,
    iconClassList: String,
    user: {
        type: Object as PropType<User>,
        required: true,
    }
})

</script>

<template>
    <span :class="borderClassList" class="icon icon-border icon-outline flex justify-center items-center">
        <img alt="" class="mt-0" :class="iconClassList" src="/images/users/content/funny-girl.png">
    </span>
</template>

<style scoped>

</style>
