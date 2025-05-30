<script setup lang="ts">
import {useToastStore} from '@/stores/toast'
import {ref} from 'vue'
import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

const props = defineProps({
    id: String,
    editable: {
        type: Boolean,
        default: true
    },
    icon: String,
    isUploading: {
        type: Boolean,
        default: false
    },
    title: String,
    files: String,
    maxSizeInMegabytes: {
        type: Number,
        required: true
    },
    uploadingItem: {
        type: String,
        default: 'item-diamond'
    }
})

const emit = defineEmits<{
    (e: 'upload', file: File): void
}>()

const input = ref<HTMLInputElement>()
const toastStore = useToastStore()

const allowedFileFormats = ['MCPACK', 'MCADDON', 'MCWORLD', 'MCSKIN', 'ZIP', 'PNG']
const maxSizeInMegabytes = props.maxSizeInMegabytes

const isDraggingOver = ref(false)

function onChange(event: any) {
    if (props.editable && event.target && event.target.files[0]) {
        uploadFile(event.target.files![0])
    }
    input.value!.value = null
}

function onDrop(event: DragEvent) {
    if (props.editable) {
        if (event.dataTransfer?.files) {
            uploadFile(event.dataTransfer.files[0])
        }
        isDraggingOver.value = false
    }
}

function onDragEnter() {
    if (props.editable) {
        isDraggingOver.value = true
    }
}

function onDragLeave() {
    if (props.editable) {
        isDraggingOver.value = false
    }
}

function uploadFile(file: File) {
    const fileExtension = file.name.split('.').pop().toUpperCase()

    if (!allowedFileFormats.includes(fileExtension)) {
        toastStore.warning(
            `Недопустимый формат Файла, разрешены только ${allowedFileFormats.join(', ')}.`,
            'Неверный формат'
        )
        return
    }

    if (file.size > maxSizeInMegabytes * 1024 * 1024) {
        toastStore.warning(
            `Файл слишком большой. Максимальный размер - ${maxSizeInMegabytes} Мб.`,
            'Размер'
        )
        return
    }

    emit('upload', file)
}
</script>

<template>
    <label
        :class="{'editable': editable, 'cursor-pointer': editable, 'dragover': isDraggingOver }"
        class="upload-file-container flex justify-center items-center locked"
        @dragenter.prevent="onDragEnter"
        @dragleave="onDragLeave"
        @dragover.prevent
        @drop.prevent="onDrop"
        :for="id"
    >
        <em
            class="upload-file-info flex flex-col items-center gap-2 py-8 px-4"
        >
        <span class="upload-file-heading flex gap-2">
            <span :class="icon" class="icon relative"></span>
            <span class="head-font text-center md:text-[16px] text-[14px] duration-200">{{ title }}</span>
        </span>
            <span class="text-center md:text-[14px] text-[12px]">Максимальный размер — {{
                    maxSizeInMegabytes
                }} Мб</span>
            <span class="text-center md:text-[14px] text-[12px]">{{ allowedFileFormats.join(' / ') }}</span>
            <ProcessingMovingItems
                v-if="isUploading"
                :item="uploadingItem"
                height="4rem"
                width="4rem"
            />
            <input
                ref="input"
                @change="onChange"
                accept="file/*"
                type="file"
                :id="id"
            />
        </em>
    </label>
</template>

<style scoped>
.upload-file-container:active .icon,
.upload-file-container:hover .icon,
.upload-file-container.dragover .icon {
    animation: levitation-icon-animation infinite 1s ease;
}

.upload-file-info {
    background-size: 100% 100%;
    pointer-events: none;
}

em {
    transition: .5s;
}

em span {
    text-shadow: none;
}

em.loaded span {
    text-shadow: 0 0 8px black;
}

em.loaded {
    opacity: 0;
}

em.loaded .upload-file-heading {
    color: var(--hover-text-color);
}

.upload-file-container:hover em.loaded:not(:hover) {
    opacity: 1;
}

.upload-file-container.loaded:not(.dragover) {
    outline: 0;
}

input {
    height: 0;
    width: 0;
}
</style>
