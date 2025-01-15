<script setup lang="ts">
import {type MaterialVersionSubmission, SubmissionType} from '@/types'
import Select, {type SelectOptionChangeEvent} from '@/components/elements/Select.vue'
import {computed, type PropType} from 'vue'

const props = defineProps({
    currentVersionSubmission: {
        type: Object as PropType<MaterialVersionSubmission>,
        required: true
    },
    versionSubmissions: {
        type: Object as PropType<MaterialVersionSubmission[]>,
        required: true
    },
})

const emit = defineEmits<{
    (e: 'change', event: SelectOptionChangeEvent): void,
}>()

function wasVersionUpdated(versionSubmission: MaterialVersionSubmission) {
    return versionSubmission.type === SubmissionType.UPDATE
        && (versionSubmission.version!.state!.number !== versionSubmission.version_state!.number
            || versionSubmission.version!.state!.localizations![0].name !== versionSubmission.version_state!.localizations![0].name)
}

function getOptionExtraClasses(versionSubmission: MaterialVersionSubmission) {
    switch (versionSubmission.type) {
        case SubmissionType.CREATE:
            return 'transfusion success'
        case SubmissionType.UPDATE:
            return wasVersionUpdated(versionSubmission) ? 'transfusion' : ''
        case SubmissionType.DELETE:
            return 'transfusion danger'
    }
}

function getVersionState(versionSubmission: MaterialVersionSubmission) {
    return versionSubmission.type === SubmissionType.DELETE
        ? versionSubmission.version!.state
        : versionSubmission.version_state
}
</script>

<template>
    <Select
        button-classes="ld-primary-background ld-primary-border ld-title-font w-full"
        options-classes="ld-primary-background ld-primary-border md:top-[66px]
                        top-[50px]"
        :option-classes="(option: MaterialVersionSubmission) => `md:min-h-[64px] min-h-[48px] gap-4 pl-6 ${getOptionExtraClasses(option)}`"
        class="material-category flex items-center w-full my-4"
        input-id="version"
        :current-option="currentVersionSubmission"
        :options="versionSubmissions"
        placeholder="Выберите версию"
        @change="(event) => emit('change', event)"
    >
        <template #option-label="{option} : {option: MaterialVersionSubmission}">
            <span class="truncate label text-start duration-200">
                {{ getVersionState(option).number }}
                {{
                    getVersionState(option).localizations[0].name ? `- ${getVersionState(option).localizations[0].name}` : ''
                }}
            </span>
        </template>
    </Select>
</template>

<style scoped>

</style>
