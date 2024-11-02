<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import {type PostVersionFile} from '@/types'
import Input from '@/components/elements/Input.vue'
import Select from '@/components/elements/Select.vue'
import axios from 'axios'

const props = defineProps({
    file: {
        type: Object as PropType<PostVersionFile>,
        required: true
    },
    editable: {
        type: Boolean,
        default: true
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['remove'])

const urlSize = ref<number|undefined>(props.file!.size ? props.file!.size / 1024 : undefined)
enum FileSizeUnit {
    KB = 'КБ',
    MB = 'МБ'
}
const fileSizeUnit = ref<FileSizeUnit>(FileSizeUnit.KB)
const fileSizeUnits = [
    FileSizeUnit.KB,
    FileSizeUnit.MB
]

function updateUrlFileSize() {
    props.file!.size = urlSize.value * 1024
    if (fileSizeUnit.value === FileSizeUnit.MB) {
        props.file!.size *= 1024
    }
}

const urlFileExtensions = [
    {icon: 'icon-spawn-egg', label: 'MCADDON', value: 'mcaddon'},
    {icon: 'icon-brilliant', label: 'MCPACK', value: 'mcpack'},
    {icon: 'icon-map', label: 'MCWORLD', value: 'mcworld'},
    {icon: 'icon-unordered-list', label: 'ZIP', value: 'zip'},
    {icon: 'icon-image', label: 'PNG', value: 'png'},
]

const isEditingFile = ref(false)
const isEditingLink = ref(false)
const isExtendedUrlProperties = ref(false)

const fileSizeLabel = computed(() => {
    if (props.file!.size === undefined) {
        return ''
    }

    const kilobytes = props.file!.size / 1024

    if (kilobytes < 1000) {
        return Math.ceil(kilobytes) + ' КБ'
    } else {
        const megabytes = kilobytes / 1024
        return megabytes.toFixed(2).toLocaleString() + ' МБ'
    }
})

function editFile() {
    isEditingFile.value = true
}

function editLink() {
    isEditingLink.value = true
}

function save() {
    isEditingFile.value = false
    isEditingLink.value = false
}

function download() {
    if (props.file!.path) {
        if (props.file!.post_version_id === undefined || props.file!.id === undefined) {
            return
        }

        axios.get(`/api/post-versions/${props.file!.post_version_id}/download/${props.file!.id}`, {responseType: 'blob'}).then((response) => {
            const href = URL.createObjectURL(response.data)

            const link = document.createElement('a')
            link.href = href
            link.download = props.file!.path!.replace(/^.*[\\/]/, '')
            document.body.appendChild(link)
            link.click()

            document.body.removeChild(link)
            URL.revokeObjectURL(href)
        })
    } else {
        window.open(props.file!.url!, '_blank')
    }
}
</script>

<template>
    <div
        class="loaded-file-background ld-primary-background ld-shadow-text flex"
        :class="{ 'disabled': disabled }"
        ref="container"
    >
        <div class="loaded-file transfusion-light flex w-full sm:gap-4 gap-2 sm:pl-4 pl-2 cursor-pointer">
            <div class="flex items-center md:h-[64px] h-[48px]">
                <span
                    class="icon-download icon min-w-[2rem]"
                    :class="{ 'icon-download': file.path, 'icon-link-square': !file.path }"
                />
            </div>
            <div v-if="file.path" class="flex md:max-h-[64px] max-h-[48px] w-full overflow-hidden">
                <div class="loaded-file-bar flex flex-col w-full duration-500"
                     :class="{ 'editing-file': isEditingFile }">
                    <span class="uploaded-file-name-input flex items-center md:min-h-[64px] min-h-[48px]">
                        <Input
                            v-model="file.name"
                            class="ld-tinted-background ld-primary-border sm:text-[14px]
                                text-[12px] md:h-[48px] h-[40px] xs:w-full w-[90%]"
                            id="post-version-file-name"
                            :max-length="80"
                            :min-length="3"
                            placeholder="Название Файла"
                        />
                        <span class="xs:flex hidden md:text-[14px] text-[12px] opacity-80 pl-2">{{
                                file.extension
                            }}</span>
                    </span>
                    <span
                        class="uploaded-file-info flex flex-col justify-center
                        md:text-[14px] text-[12px] md:min-h-[64px] min-h-[48px]"
                        @click="download"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ file.name }}</span>
                        </span>
                        <span class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{ `${file.extension ? file.extension.toUpperCase() + ' — ' : ''}` + `${fileSizeLabel || ''}` }}
                        </span>
                    </span>
                </div>
            </div>
            <div
                v-else
                class="loaded-link-span flex flex-col sm:w-full duration-500"
                :class="{
                    'md:max-h-[278px] max-h-[282px] overflow-link-animation': isEditingLink && isExtendedUrlProperties,
                    'md:max-h-[128px] max-h-[96px] overflow-hidden': isEditingLink && !isExtendedUrlProperties,
                    'md:max-h-[64px] max-h-[48px] overflow-hidden': !isEditingLink
                }"
                style="width: calc(100% - 136px);"
            >
                <div
                    class="loaded-link-bar flex flex-col w-full duration-500"
                    :class="{ 'editing-link': isEditingLink }"
                >
                    <span
                        v-if="!isEditingLink"
                        class="uploaded-file-info flex flex-col justify-center
                            md:text-[14px] text-[12px] md:min-h-[64px] min-h-[48px]"
                        @click="download"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ file.name }}</span>
                        </span>
                        <span class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{ `${file.extension ? file.extension.toUpperCase() + ' — ' : ''}` + `${file.size ? fileSizeLabel + ' — ' : ''}` + file.url }}
                        </span>
                    </span>
                    <span class="uploaded-file-name-input flex items-center md:min-h-[64px] min-h-[48px]">
                        <Input
                            v-model="file.name"
                            class="ld-tinted-background ld-primary-border sm:text-[14px] text-[12px]
                                md:h-[48px] h-[40px] w-full"
                            id="post-version-file-name"
                            :max-length="80"
                            :min-length="3"
                            placeholder="Название Файла"
                        />
                    </span>
                    <span class="uploaded-file-url-input flex items-center md:h-[64px] h-[48px]">
                        <Input
                            v-model="file.url"
                            class="ld-tinted-background ld-primary-border sm:text-[14px] text-[12px]
                                md:h-[48px] h-[40px] w-full"
                            id="post-version-file-name"
                            :max-length="255"
                            :min-length="3"
                            placeholder="Ссылка на Файл"
                        />
                    </span>
                </div>
                <div v-if="isExtendedUrlProperties" class="max-w-full w-full">
                    <span class="uploaded-file-url-input flex items-center md:h-[64px] h-[48px]">
                        <Input
                            v-model="urlSize"
                            class="ld-tinted-background ld-primary-border sm:text-[14px] text-[12px]
                                md:h-[48px] h-[40px] w-full"
                            id="post-version-file-name"
                            :max-length="8"
                            :min-length="3"
                            placeholder="Вес Файла"
                            @change="updateUrlFileSize"
                        />
                        <Select
                            button-classes="ld-primary-background ld-primary-border ld-title-font w-full
                                whitespace-nowrap text-[14px]"
                            options-classes="ld-primary-background ld-primary-border top-[50px]"
                            option-classes="text-[14px] md:min-h-[48px] min-h-[40px] gap-4 sm:pl-6 pl-2"
                            class="post-edition flex items-center w-[200px]"
                            v-model="fileSizeUnit"
                            :editable="editable"
                            input-id="edition"
                            :options="fileSizeUnits"
                            @change="updateUrlFileSize"
                        >
                            <template #option-icon/>
                        </Select>
                    </span>
                    <div class="flex sm:flex-row flex-col gap-2 mb-2" style="z-index: 1">

                        <Select
                            button-classes="ld-primary-background ld-primary-border ld-title-font w-full
                                whitespace-nowrap text-[14px]"
                            options-classes="ld-primary-background ld-primary-border top-[50px]"
                            option-classes="text-[14px] md:min-h-[48px] min-h-[40px] gap-4 sm:pl-6 pl-2"
                            class="post-edition flex items-center w-full"
                            placeholder="Расширение Файла"
                            v-model="file.extension"
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
            <input class="hidden" :disabled="disabled" type="file">
            <div class="flex justify-end min-w-[96px]">
                <button
                    v-if="editable && !file.path && isEditingLink"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Дополнительно'"
                    @click="isExtendedUrlProperties = !isExtendedUrlProperties"
                >
                    <span class="icon flex" :class="{ 'icon-eye': isExtendedUrlProperties, 'icon-eye-cross': !isExtendedUrlProperties }"/>
                </button>
                <button
                    v-if="editable && (isEditingFile || isEditingLink)"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px] pr-3"
                    v-tooltip.top="'Готово'"
                    @click="save"
                >
                    <span class="icon-tick icon flex"/>
                </button>
                <button
                    v-if="editable && file.path && !isEditingFile"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Изменить Файл'"
                    @click="editFile"
                >
                    <span class="icon-small-pencil icon flex"/>
                </button>
                <button
                    v-if="editable && !file.path && !isEditingLink"
                    class="button-edit flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Изменить Ссылку'"
                    @click="editLink"
                >
                    <span class="icon-small-pencil icon flex"/>
                </button>
                <button
                    v-if="editable && !isEditingFile && !isEditingLink"
                    class="button-cross flex justify-center items-center md:h-[64px] h-[48px] sm:min-w-[48px] min-w-[32px]"
                    v-tooltip.top="'Удалить'"
                    @click="emit('remove')"
                >
                    <span class="icon-trash icon flex"/>
                </button>
            </div>
        </div>
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
