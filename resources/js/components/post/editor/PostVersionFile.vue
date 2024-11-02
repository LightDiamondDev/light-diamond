<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import type {PostVersionFile} from '@/types'
import Input from '@/components/elements/Input.vue'
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
const model = defineModel()

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

function download() {
    if (props.file!.path) {
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
    <a
        :class="{ 'disabled': disabled }"
        class="loaded-file-background ld-primary-background ld-shadow-text flex"
        :download="file.path"
        ref="container"
        @click="download"
    >
        <div class="loaded-file transfusion-light flex w-full">
            <label
                class="loaded-file-label flex items-center md:h-[64px] h-[48px] w-full
                    sm:gap-4 gap-2 sm:pl-4 pl-2 overflow-hidden cursor-pointer" for=""
            >
                <span class="flex items-center md:min-h-[64px] min-h-[48px]">
                    <span
                        class="icon-download icon min-w-[2rem]"
                        :class="{'icon-download': file.path, 'icon-link-square': !file.path }"
                    />
                </span>
                <span
                    class="flex flex-col w-full duration-500"
                    :class="{
                        'loaded-file-span': file.path,
                        'loaded-link-span': file.url
                    }"
                >
                    <span
                        class="uploaded-file-info flex flex-col justify-center
                            md:text-[14px] text-[12px] md:h-[64px] h-[48px]"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ file.name }}</span>
                        </span>
                        <span v-if="file.path" class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{ `${file.extension.toUpperCase()} — ${fileSizeLabel}` }}
                        </span>
                        <span v-else class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{ `${file.extension ? file.extension.toUpperCase() + (file.size ? ' — ' : '') : ''}` + `${file.size ? fileSizeLabel : ''}` }}
                        </span>
                    </span>
                </span>
                <input class="hidden" :disabled="disabled" type="file">
            </label>
        </div>
    </a>
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
