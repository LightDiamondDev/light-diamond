<script setup lang="ts">
import {computed, type PropType, reactive, ref} from 'vue'
import {type MaterialFileSubmission, SubmissionType} from '@/types'
import Input from '@/components/elements/Input.vue'
import Select from '@/components/elements/Select.vue'
import axios, {type AxiosError} from 'axios'
import Button from '@/components/elements/Button.vue'
import {withCaptcha, getErrorMessageByCode, getFullPresentableDate, getRelativeDate} from '@/helpers'
import {useToastStore} from '@/stores/toast'

const props = defineProps({
    fileSubmission: {
        type: Object as PropType<MaterialFileSubmission>,
        required: true
    },
    versionSubmission: {
        type: Object as PropType<MaterialFileSubmission>,
        required: true
    },
    editable: {
        type: Boolean,
        default: true
    },
})

export interface FileUpdateEvent {
    fileSubmission: MaterialFileSubmission
    data: FileUpdateData
    cancel: boolean
}

interface FileUpdateData {
    name?: string
    path?: string | null
    url?: string | null
    size?: number | null
    extension?: string | null
}

const emit = defineEmits(['update', 'remove'])

const fileSizeInKB = computed(() => props.fileSubmission!.file!.size / 1024)
const fileSizeInMB = computed(() => fileSizeInKB.value / 1024)
const localization = computed(() => {
    const state = props.fileSubmission!.type === SubmissionType.DELETE
        ? props.fileSubmission!.file!.state
        : props.fileSubmission!.file_state

    return state!.localizations![0]
})
const isUpdated = computed(() => {
    return props.fileSubmission!.type === SubmissionType.UPDATE
        && props.fileSubmission!.file!.state?.localizations![0].name !== props.fileSubmission!.file_state?.localizations![0].name
})
const isRemoved = computed(() => props.fileSubmission!.type === SubmissionType.DELETE || props.versionSubmission!.type === SubmissionType.DELETE)

const urlSize = ref<number | undefined>(
    props.fileSubmission!.file!.size
        ? fileSizeInKB.value < 1000 ? fileSizeInKB.value : fileSizeInMB.value
        : undefined
)

enum FileSizeUnit {
    KB = 'КБ',
    MB = 'МБ'
}

const fileSizeUnit = ref<FileSizeUnit>(fileSizeInKB.value < 1000 ? FileSizeUnit.KB : FileSizeUnit.MB)
const fileSizeUnits = [
    FileSizeUnit.KB,
    FileSizeUnit.MB
]

function updateUrlFileSize() {
    fileUpdateData.size = Math.round(urlSize.value * 1024)
    if (fileSizeUnit.value === FileSizeUnit.MB) {
        fileUpdateData.size *= 1024
    }
}

const urlFileExtensions = [
    {icon: 'icon-spawn-egg', label: 'MCADDON', value: 'mcaddon'},
    {icon: 'icon-brilliant', label: 'MCPACK', value: 'mcpack'},
    {icon: 'icon-map', label: 'MCWORLD', value: 'mcworld'},
    {icon: 'icon-unordered-list', label: 'ZIP', value: 'zip'},
    {icon: 'icon-image', label: 'PNG', value: 'png'},
]

const isEditing = ref(false)
const isExtendedUrlProperties = ref(false)
const fileUpdateData = reactive<FileUpdateData>({})

const isUrl = computed(() => !props.fileSubmission!.file!.path)
const fileSizeLabel = computed(() => {
    if (props.fileSubmission!.file!.size === undefined) {
        return ''
    }

    if (fileSizeInKB.value < 1000) {
        return Math.ceil(fileSizeInKB.value) + ' КБ'
    } else {
        return parseFloat(fileSizeInMB.value.toFixed(2)).toLocaleString() + ' МБ'
    }
})

function edit() {
    resetFileUpdateData()
    isEditing.value = true
}

function cancelEdit() {
    isEditing.value = false
}

function remove() {
    emit('remove', props.fileSubmission)
}

function save() {
    const wasUpdated = localization.value.name !== fileUpdateData.name
        || props.fileSubmission!.file!.path !== fileUpdateData.path
        || props.fileSubmission!.file!.url !== fileUpdateData.url
        || props.fileSubmission!.file!.size !== fileUpdateData.size
        || props.fileSubmission!.file!.extension !== fileUpdateData.extension

    if (wasUpdated) {
        const event: FileUpdateEvent = {
            fileSubmission: props.fileSubmission!,
            data: fileUpdateData,
            cancel: false
        }

        emit('update', event)

        if (!event.cancel) {
            isEditing.value = false
        }
    } else {
        isEditing.value = false
    }
}

function download() {
    if (props.fileSubmission!.file!.url) {
        window.open(props.fileSubmission!.file!.url!, '_blank')
    } else {
        withCaptcha(() => {
            const versionId = props.fileSubmission!.file!.version_id
            const fileId = props.fileSubmission!.file!.id

            if (versionId === undefined || fileId === undefined) {
                return
            }

            axios.get(`/api/material-versions/${versionId}/download/${fileId}`, {responseType: 'blob'}).then((response) => {
                const href = URL.createObjectURL(response.data)

                const link = document.createElement('a')
                link.href = href
                link.download = props.fileSubmission!.file!.path!.replace(/^.*[\\/]/, '')
                document.body.appendChild(link)
                link.click()

                document.body.removeChild(link)
                URL.revokeObjectURL(href)
            }).catch((error: AxiosError) => {
                useToastStore().error(getErrorMessageByCode(error.response!.status))
            })
        })
    }
}

function resetFileUpdateData() {
    fileUpdateData.name = localization.value.name
    fileUpdateData.path = props.fileSubmission!.file!.path
    fileUpdateData.url = props.fileSubmission!.file!.url
    fileUpdateData.size = props.fileSubmission!.file!.size
    fileUpdateData.extension = props.fileSubmission!.file!.extension
}
</script>

<template>
    <div
        class="loaded-file-background ld-primary-background ld-shadow-text flex"
        :class="{ 'disabled': !editable }"
        ref="container"
    >
        <form
            class="loaded-file flex w-full sm:gap-4 gap-2 sm:pl-4 pl-2 cursor-pointer"
            :class="{
                'transfusion': fileSubmission.type === SubmissionType.UPDATE && isUpdated,
                'transfusion success': fileSubmission.type === SubmissionType.CREATE,
                'transfusion danger': isRemoved
            }"
            @submit.prevent="save" @keydown.esc="isEditing = false"
        >
            <div class="flex items-center md:h-[64px] h-[48px]">
                <span
                    class="icon-download icon min-w-[2rem]"
                    :class="{ 'icon-download': !isUrl, 'icon-link-square': isUrl }"
                />
            </div>

            <div
                v-if="isUrl"
                class="loaded-link-span flex flex-col sm:w-full duration-500"
                :class="{
                    'md:max-h-[278px] max-h-[282px] overflow-link-animation': isEditing && isExtendedUrlProperties,
                    'md:max-h-[128px] max-h-[96px] overflow-hidden': isEditing && !isExtendedUrlProperties,
                    'md:max-h-[64px] max-h-[48px] overflow-hidden': !isEditing
                }"
                style="width: calc(100% - 136px);"
            >
                <div
                    class="loaded-link-bar flex flex-col w-full duration-500"
                    :class="{ 'editing-link': isEditing }"
                >
                    <template v-if="isEditing">
                        <span class="uploaded-file-name-input flex items-center md:min-h-[64px] min-h-[48px]">
                            <Input
                                v-model="fileUpdateData.name"
                                class="ld-tinted-background ld-primary-border sm:text-[14px] text-[12px]
                                    md:h-[48px] h-[40px] w-full"
                                id="material-file-name"
                                :max-length="80"
                                :min-length="3"
                                placeholder="Название Файла"
                            />
                        </span>
                        <span
                            v-if="fileSubmission.type === SubmissionType.CREATE"
                            class="uploaded-file-url-input flex items-center md:h-[64px] h-[48px]"
                        >
                            <Input
                                v-model="fileUpdateData.url"
                                class="ld-tinted-background ld-primary-border sm:text-[14px] text-[12px]
                                    md:h-[48px] h-[40px] w-full"
                                id="material-file-name"
                                :max-length="255"
                                :min-length="3"
                                placeholder="Ссылка на Файл"
                            />
                        </span>
                    </template>
                    <span
                        v-else
                        class="uploaded-file-info flex flex-col justify-center
                            md:text-[14px] text-[12px] md:min-h-[64px] min-h-[48px]"
                        @click="download"
                    >

                        <span class="-mb-1 truncate w-fit max-w-[90%]">
                            <span class="file-name">{{ localization.name }}</span>
                        </span>
                        <span class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{
                                `${fileSubmission.file.extension ? fileSubmission.file.extension.toUpperCase() + ' — ' : ''}` + `${fileSubmission.file.size ? fileSizeLabel + ' — ' : ''}` + fileSubmission.file.url
                            }}
                        </span>
                    </span>
                </div>
                <div v-if="isExtendedUrlProperties && fileSubmission.type === SubmissionType.CREATE"
                     class="max-w-full w-full">
                    <span class="uploaded-file-url-input flex items-center md:h-[64px] h-[48px]">
                        <Input
                            v-model="urlSize"
                            class="ld-tinted-background ld-primary-border-bottom ld-primary-border-top
                                ld-primary-border-left sm:text-[14px] text-[12px] md:h-[48px] h-[40px] w-full"
                            id="material-file-name"
                            :max-length="8"
                            :min-length="3"
                            numeric
                            placeholder="Вес Файла"
                            @change.prevent="updateUrlFileSize"
                        />
                        <Select
                            button-classes="ld-primary-background ld-primary-border ld-title-font w-full
                                whitespace-nowrap text-[14px] md:h-[48px] h-[40px]"
                            options-classes="ld-primary-background ld-primary-border md:top-[46px] top-[38px]"
                            option-classes="text-[14px] md:min-h-[48px] min-h-[40px] gap-4 sm:pl-6 pl-2"
                            class="material-edition flex items-center w-[200px]"
                            v-model="fileSizeUnit"
                            :editable="editable"
                            input-id="edition"
                            :options="fileSizeUnits"
                            @change="updateUrlFileSize"
                        >
                            <template #option-icon/>
                        </Select>
                    </span>
                    <div class="flex sm:flex-row flex-col gap-2 my-2 z-[1]">
                        <Select
                            button-classes="ld-primary-background ld-primary-border ld-title-font w-full
                                whitespace-nowrap text-[14px] md:h-[48px] h-[40px]"
                            options-classes="ld-primary-background ld-primary-border md:top-[46px] top-[38px]"
                            option-classes="text-[14px] md:min-h-[48px] min-h-[40px] gap-4 sm:pl-6 pl-2"
                            class="material-edition flex items-center w-full"
                            placeholder="Расширение Файла"
                            v-model="fileUpdateData.extension"
                            :editable="editable"
                            input-id="edition"
                            :options="urlFileExtensions"
                            option-icon-key="icon"
                            option-label-key="value"
                            option-value-key="value"
                        >
                            <template #option-icon/>
                        </Select>
                    </div>
                </div>
            </div>
            <div v-else class="flex md:max-h-[64px] max-h-[48px] w-full overflow-hidden">
                <div class="loaded-file-bar flex flex-col w-full duration-500"
                     :class="{ 'editing-file': isEditing }">
                    <span class="uploaded-file-name-input flex items-center md:min-h-[64px] min-h-[48px]">
                        <Input
                            v-model="fileUpdateData.name"
                            class="ld-tinted-background ld-primary-border sm:text-[14px]
                                text-[12px] md:h-[48px] h-[40px] xs:w-full w-[90%]"
                            id="material-file-name"
                            :max-length="80"
                            :min-length="3"
                            placeholder="Название Файла"
                        />
                        <span class="xs:flex hidden md:text-[14px] text-[12px] opacity-80 pl-2">
                            {{ fileSubmission.file.extension }}</span>
                    </span>
                    <span
                        class="uploaded-file-info flex flex-col justify-center
                        md:text-[14px] text-[12px] md:min-h-[64px] min-h-[48px]"
                        @click="download"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ localization.name }}</span>
                        </span>
                        <span class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{
                                `${fileSubmission.file.extension ? fileSubmission.file.extension.toUpperCase() + ' — ' : ''}` + `${fileSizeLabel || ''}`
                            }}
                        </span>
                    </span>
                </div>
            </div>

            <span
                v-if="!isEditing && editable && fileSubmission.type === SubmissionType.CREATE"
                :title="getFullPresentableDate(fileSubmission.file!.updated_at!)"
                class="hidden xs:block text-xs opacity-70 shrink-0 self-center"
            >
                {{ getRelativeDate(fileSubmission.file.updated_at) }}
            </span>
            <div v-if="!isRemoved" class="flex justify-end">
                <button
                    v-if="editable && !isEditing"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="`Изменить ${isUrl ? 'Ссылку' : 'Файл'}`"
                    type="button"
                    @click="edit"
                >
                    <span class="icon-small-pencil icon flex"/>
                </button>
                <button
                    v-if="editable && !isEditing"
                    class="button-cross flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Удалить'"
                    type="button"
                    @click="remove"
                >
                    <span class="icon-trash icon flex"/>
                </button>
                <button
                    v-if="editable && isUrl && isEditing && fileSubmission.type === SubmissionType.CREATE"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Дополнительно'"
                    type="button"
                    @click="isExtendedUrlProperties = !isExtendedUrlProperties"
                >
                    <span class="icon flex"
                          :class="{ 'icon-eye': isExtendedUrlProperties, 'icon-eye-cross': !isExtendedUrlProperties }"/>
                </button>
                <button
                    v-if="editable && isEditing"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Готово'"
                    type="submit"
                >
                    <span class="icon-tick icon flex"/>
                </button>
                <button
                    v-if="editable && isEditing"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Отмена'"
                    type="button"
                    @click="cancelEdit"
                >
                    <span class="icon-small-cross icon flex"/>
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.tooltip::before {
    white-space: nowrap;
    margin-top: 8px;
    height: 2rem;
}

.tooltip::after {
    top: 8px;
}

.loaded-file-bar {
    transform: translateY(-100%);
}

.loaded-file-bar.editing-file {
    transform: translateY(0);
}

.loaded-file:hover .icon-download {
    animation: icon-trigger-up-animation .3s ease;
}

.loaded-file-label span {
    line-height: 1.5;
}

.loaded-file:hover .file-name {
    color: var(--hover-text-color);
}

.overflow-link-animation {
    animation: overflow-link-animation .5s ease;
}

/* =============== [ Анимации ] =============== */

@keyframes overflow-link-animation {
    0% {
        overflow: hidden;
    }
    100% {
        overflow: hidden;
    }
}
</style>
