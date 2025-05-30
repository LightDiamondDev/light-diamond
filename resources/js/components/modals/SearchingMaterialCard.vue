<script setup lang="ts">

import type {Material} from '@/types'
import {computed, type PropType} from 'vue'
import useCategoryRegistry from '../../categoryRegistry'

const props = defineProps({
    material: {
        type: Object as PropType<Material>,
        required: true
    },
    term: {
        type: String,
        required: true
    }
})

function highlightText(text, term) {
    if (!term) return text
    const regex = new RegExp(`(${term})`, 'gi')
    return text.replace(regex, '<i class="ld-term-text">$1</i>')
}

const searchingCardTitle = computed(() => (
    highlightText(props.material!.state!.localization!.title, props.term)
))

const searchingCardDescription = computed(() => (
    highlightText(props.material!.state!.localization!.description, props.term)
))

const materialRoute = computed(() => ({
    name: 'material',
    params: {
        edition: props.material!.edition?.toLowerCase(),
        category: useCategoryRegistry().get(props.material!.category)!.slug,
        slug: props.material!.slug
    }
}))

</script>

<template>
    <RouterLink
        class="material-search-card ld-primary-background border-0
            flex items-center min-h-[64px] w-full"
        :to="materialRoute"
    >
        <div class="preview-wrap flex min-w-[112px] overflow-hidden">
            <img
                alt="Превью Материала"
                class="preview flex max-h-[60px] w-full full-locked duration-200"
                :src="material.state!.localization!.cover_url"
                style="aspect-ratio: 16/9; object-fit: cover"
            >
        </div>
        <div class="description ld-primary-border flex justify-center items-center h-full min-w-[48px] w-full">
            <div
                class="flex flex-col justify-center max-h-[72px]
                    h-full w-full pl-2 overflow-hidden relative"
            >
                <h3
                    class="text-[16px] truncate absolute w-full top-0.5"
                    v-html="searchingCardTitle"
                />
                <p
                    class="text-[12px] truncate absolute w-full bottom-[18px] ld-lightgray-text"
                    v-html="searchingCardDescription"
                />
                <p class="text-[12px] truncate absolute w-full bottom-0 ld-brilliant-text opacity-80">
                    {{ useCategoryRegistry().get(material.category).singularName }}
                    <!--                    16x16 1.21+-->
                </p>
            </div>
            <div class="flex justify-center min-w-[3rem]">
                <span class="icon icon-right-arrow flex mr-[8px] duration-100"/>
            </div>
        </div>
    </RouterLink>
</template>

<style scoped>
.material-search-card:hover .preview {
    transform: scale(1.2);
}

.material-search-card:hover h3 {
    color: var(--hover-text-color);
}

.material-search-card:hover .icon-right-arrow {
    margin-left: 8px;
}
</style>
