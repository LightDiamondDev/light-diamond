<script setup lang="ts">
import type {MaterialVersion} from '@/types'
import Select, {type SelectOptionChangeEvent} from '@/components/elements/Select.vue'
import {type PropType} from 'vue'

const props = defineProps({
    currentVersion: null,
    versions: {
        type: Object as PropType<MaterialVersion[]>,
        required: true
    },
})

const emit = defineEmits<{
    (e: 'change', event: SelectOptionChangeEvent): void,
}>()
const versionModel = defineModel<MaterialVersion>({default: {}})
</script>

<template>
    <Select
        button-classes="ld-primary-background ld-primary-border ld-title-font w-full"
        options-classes="ld-primary-background ld-primary-border md:top-[66px]
                        top-[50px]"
        option-classes="md:min-h-[64px] min-h-[48px] gap-4 pl-6"
        class="material-category flex items-center w-full my-4"
        v-model="versionModel"
        :current-option="currentVersion ?? undefined"
        input-id="version"
        :options="versions"
        placeholder="Выберите версию"
        @change="(event) => emit('change', event)"
    >
        <template #option-label="{option} : {option: MaterialVersion}">
            <span class="truncate label text-start duration-200">
                {{ option.state.number }}
                {{ option.state.localizations[0].name ? `- ${option.state.localizations[0].name}` : '' }}
            </span>
        </template>
    </Select>
</template>
