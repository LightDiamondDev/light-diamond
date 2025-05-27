<script setup lang="ts">
import type {PropType} from 'vue'
import type {User} from '@/types'

defineProps({
    borderClassList: String,
    iconClassList: String,
    user: {
        type: Object as PropType<User>,
        default: null,
    }
})

</script>

<template>
    <span :class="borderClassList" class="icon icon-border icon-outline flex justify-center items-center">
        <img
            alt=""
            :src="user.avatar_url ?? '/images/users/default-avatar.png'"
            class="mt-0"
            :class="iconClassList"
        >
    </span>
</template>
