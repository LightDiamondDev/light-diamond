<script setup lang="ts">
import {computed, type PropType} from 'vue'
import {type MaterialFile} from '@/types'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'

const props = defineProps({
    file: {
        type: Object as PropType<MaterialFile>,
        required: true
    },
})

const model = defineModel()


const isUrl = computed(() => !props.file!.path)
const fileSizeInKB = computed(() => props.file!.size / 1024)
const fileSizeInMB = computed(() => fileSizeInKB.value / 1024)
const fileSizeLabel = computed(() => {
    if (props.file!.size === undefined) {
        return ''
    }

    if (fileSizeInKB.value < 1000) {
        return Math.ceil(fileSizeInKB.value) + ' КБ'
    } else {
        return parseFloat(fileSizeInMB.value.toFixed(2)).toLocaleString() + ' МБ'
    }
})

function download() {
    axios
        .get(`/api/material-versions/${props.file!.version_id}/download/${props.file!.id}`, {responseType: isUrl.value ? 'json' : 'blob'})
        .then((response) => {
            if (isUrl.value) {
                window.open(response.data.download_url, '_blank')
            } else {
                const href = URL.createObjectURL(response.data)

                const link = document.createElement('a')
                link.href = href
                link.download = props.file!.path!.replace(/^.*[\\/]/, '')
                document.body.appendChild(link)
                link.click()

                document.body.removeChild(link)
                URL.revokeObjectURL(href)
            }
        })
        .catch((error: AxiosError) => {
            useToastStore().error(getErrorMessageByCode(error.response!.status))
        })
}
</script>

<template>
    <div
        class="loaded-file-background ld-primary-background ld-shadow-text flex"
    >
        <div class="loaded-file flex w-full sm:gap-4 gap-2 sm:pl-4 pl-2 cursor-pointer">
            <div class="flex items-center md:h-[64px] h-[48px]">
                <span
                    class="icon-download icon min-w-[2rem]"
                    :class="{ 'icon-download': !isUrl, 'icon-link-square': isUrl }"
                />
            </div>

            <div
                v-if="isUrl"
                class="loaded-link-span flex flex-col sm:w-full duration-500"
                style="width: calc(100% - 136px);"
            >
                <div
                    class="loaded-link-bar flex flex-col w-full duration-500"
                >
                    <span
                        class="uploaded-file-info flex flex-col justify-center
                            md:text-[14px] text-[12px] md:min-h-[64px] min-h-[48px]"
                        @click="download"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ file.state.localizations[0]!.name }}</span>
                        </span>
                        <span class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{
                                `${file.extension ? file.extension.toUpperCase() + ' — ' : ''}` + `${file.size ? fileSizeLabel : ''}`
                            }}
                        </span>
                    </span>
                </div>
            </div>
            <div v-else class="flex md:max-h-[64px] max-h-[48px] w-full overflow-hidden">
                <div class="loaded-file-bar flex flex-col w-full duration-500">
                    <span
                        class="uploaded-file-info flex flex-col justify-center
                        md:text-[14px] text-[12px] md:min-h-[64px] min-h-[48px]"
                        @click="download"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ file.state.localizations![0].name }}</span>
                        </span>
                        <span class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{
                                `${file.extension ? file.extension.toUpperCase() + ' — ' : ''}` + `${fileSizeLabel || ''}`
                            }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.loaded-file:hover .icon-download {
    animation: icon-trigger-up-animation .3s ease;
}

.loaded-file-label span {
    line-height: 1.5;
}

.loaded-file:hover .file-name {
    color: var(--hover-text-color);
}
</style>
