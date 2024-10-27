<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import type {PostVersionFile} from '@/types'
import Input from '@/components/elements/Input.vue'

const props = defineProps({
    as: {
        type: String,
        default: 'button'
    },
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

const isEditingName = ref(false)
const isEditingUrl = ref(false)

const fileExtension = computed(
    () => props.file!.path ? props.file!.path.slice(props.file!.path.lastIndexOf('.') + 1) : ''
)

const fileExtensionUpperCase = computed(
    () => props.file!.path ? props.file!.path.slice(props.file!.path.lastIndexOf('.') + 1).toUpperCase() : ''
)

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
</script>

<template>
    <Component
        :class="{ 'disabled': disabled }"
        class="loaded-file-background ld-primary-background ld-shadow-text flex"
        ref="container"
        :is="as"
    >
        <div class="loaded-file flex w-full">
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
                    <span class="uploaded-file-name-input flex items-center md:h-[64px] h-[48px]">
                        <Input
                            v-model="file.name"
                            class="ld-tinted-background ld-primary-border sm:text-[14px]
                                text-[12px] md:h-[48px] h-[40px] xs:w-full w-[90%] hidden"
                            id="post-version-file-name"
                            :max-length="80"
                            :min-length="3"
                            placeholder="Название Файла"
                        />
                        <span
                            v-if="file.path"
                            class="xs:flex hidden md:text-[14px] text-[12px] opacity-80 xs:min-w-[80px] pl-2"
                        >
                            {{ fileExtension }}
                        </span>
                    </span>
                    <span v-if="!file.path" class="uploaded-file-url-input flex items-center md:h-[64px] h-[48px]">
                        <Input
                            v-model="file.url"
                            class="ld-tinted-background ld-primary-border sm:text-[14px] text-[12px]
                                md:h-[48px] h-[40px] xs:w-full w-[80%] hidden"
                            id="post-version-file-name"
                            :max-length="80"
                            :min-length="3"
                            placeholder="Ссылка на Файл"
                        />
                    </span>
                    <span
                        class="uploaded-file-info flex flex-col justify-center
                            md:text-[14px] text-[12px] md:h-[64px] h-[48px]"
                    >
                        <span class="overflow-hidden -mb-1 truncate xs:max-w-[90%] max-w-[70%]">
                            <span class="file-name">{{ file.name }}</span>
                        </span>
                        <span v-if="file.path" class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{ `${fileExtension ? fileExtensionUpperCase + ' — ' : ''}` + `${fileSizeLabel || ''}` }}
                        </span>
                        <span v-else class="title-font truncate opacity-80 xs:max-w-[90%] max-w-[70%]">
                            {{ file.url }}
                        </span>
                    </span>
                </span>
                <input class="hidden" :disabled="disabled" type="file">
            </label>
        </div>
    </Component>
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
