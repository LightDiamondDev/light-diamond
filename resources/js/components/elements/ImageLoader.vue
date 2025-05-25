<script setup lang="ts">

import {ref} from 'vue'
import {useToastStore} from '@/stores/toast'

const props = defineProps({
    editable: {
        default: true,
        type: Boolean
    },
    fillerIcon: String,
    id: String,
    maxSizeInMegabytes: {
        type: Number,
        required: true
    },
    imagePath: String
})

const emit = defineEmits<{
    (e: 'upload', file: File): void
}>()

const toastStore = useToastStore()
const allowedFileFormats = ['JPG', 'JPEG', 'PNG']
const allowedFormats = allowedFileFormats.map((format) => 'image/' + format.toLowerCase())
const maxSizeInMegabytes = props.maxSizeInMegabytes
const input = ref<HTMLInputElement>()

function onChange(event: Event) {
    if (props.editable && event.target && event.target.files[0]) {
        uploadFile(event.target.files![0])
    }
    input.value!.value = null
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
        class="image-loader flex items-center locked"
        :style="{ 'background-image': `url(${imagePath})` }"
        :for="id"
    >
        <span class="filler flex justify-center items-center w-full">
            <span :class="fillerIcon" class="icon"/>
        </span>
        <input class="hidden" :id="id" ref="input" type="file" :accept="allowedFormats" @change="onChange">
    </label>
</template>

<style scoped>
.image-loader {
    background-size: 100% 100%;
    position: relative;
    cursor: pointer;
}

.image-loader .filler {
    background-color: rgba(0, 0, 0, .7);
    position: absolute;
    transition: .2s;
    bottom: 0;
    height: 0;
}

.image-loader:hover .filler {
    height: 100%;
}

.image-loader .filler .icon {
    transition: .2s;
    opacity: 0;
}

.image-loader:hover .filler .icon {
    transition: 1s;
    opacity: 1;
}
</style>
