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
    <span class="icon icon-border icon-outline flex justify-center items-center">
        <img alt="" src="/images/users/content/funny-girl.png">
    </span>
</template>

<style scoped>
.icon-border {
    height: 48px;
    width: 48px;
}
.icon-border img {
    height: 32px;
}
</style>
