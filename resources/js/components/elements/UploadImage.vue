<script setup lang="ts">
import {useToastStore} from '@/stores/toast'
import {ref} from 'vue'

const props = defineProps({
    allowedGif: {
        type: Boolean,
        default: false
    },
    id: String,
    editable: {
        type: Boolean,
        default: true
    },
    icon: String,
    title: String,
    maxSizeInMegabytes: {
        type: Number,
        required: true
    },
    minHeight: {
        type: Number,
        required: true
    },
    minWidth: {
        type: Number,
        required: true
    },
})

const emit = defineEmits<{
    (e: 'upload', file: File): void
}>()

const model = defineModel<string>({default: ''})
const toastStore = useToastStore()
const coverSrc = ref('')

const allowedImageFormats = props.allowedGif ? ['PNG', 'JPG', 'JPEG', 'GIF'] : ['PNG', 'JPG', 'JPEG']
const allowedFormats = allowedImageFormats.map((format) => 'image/' + format.toLowerCase())
const maxSizeInMegabytes = props.maxSizeInMegabytes
const minHeight = props.minHeight
const minWidth = props.minWidth

const isDraggingOver = ref(false)

function onChange(event: any) {
    if (props.editable && event.target && event.target.files[0]) {
        uploadImage(event.target.files![0])
    }
}

function onDrop(event: DragEvent) {
    if (props.editable) {
        if (event.dataTransfer?.files) {
            uploadImage(event.dataTransfer.files[0])
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

function uploadImage(file: File) {
    if (!allowedFormats.includes(file.type)) {
        toastStore.warning(
            `Недопустимый формат изображения, разрешены только ${allowedImageFormats.join(', ')}.`,
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

    const tempImg = new Image()
    tempImg.onload = () => {
        if (tempImg.width < minWidth || tempImg.height < minHeight) {
            toastStore.warning(
                `Минимальное разрешение изображения - ${minWidth} x ${minHeight}.`,
                'Разрешение'

            )
            return
        }

        emit('upload', file)
        coverSrc.value = URL.createObjectURL(file)
    }
    tempImg.src = URL.createObjectURL(file)
}
</script>

<template>
<label
    :class="{'editable': editable, 'cursor-pointer': editable, 'dragover': isDraggingOver}"
    class="upload-image-container flex justify-center items-center locked"
    :style="'background-size: 100% 100%;background-image: url(' + coverSrc + ');'"
    @dragenter.prevent="onDragEnter"
    @dragleave="onDragLeave"
    @dragover.prevent
    @drop.prevent="onDrop"
    :for="id"
>
    <span :class="{ 'loaded': coverSrc }" class="upload-image-info flex flex-col items-center gap-2 p-4">
        <span class="upload-image-heading flex gap-2">
            <span :class="icon" class="icon relative"></span>
            <span class="head-font text-[1.2rem] duration-200">{{ title }}</span>
        </span>
        <span>Минимальное разрешение — {{ minWidth }} × {{ minHeight }}</span>
        <span>Максимальный размер — {{ maxSizeInMegabytes }} Мб</span>
        <span>{{ allowedImageFormats.join(" / ") }}</span>
        <input
            @change="onChange"
            accept="image/*"
            type="file"
            :id="id"
        />
    </span>
</label>
</template>

<style scoped>
.upload-image-container:active .icon,
.upload-image-container:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.upload-image-container.dragover .icon {
    animation: levitation-icon-animation infinite 1s ease;
}
.upload-image-container.dragover .upload-image-heading,
.upload-image-container:hover .upload-image-heading {
    color: var(--hover-text-color);
}
.upload-image-info {
    background-size: 100% 100%;
    pointer-events: none;
}
.upload-image-info.loaded {
    background: radial-gradient(rgba(0, 0, 0, .6), rgba(0, 0, 0, .6), rgba(0, 0, 0, .1));
    box-shadow: 0 0 2em rgba(0, 0, 0, .6);
}
input {
    height: 0;
    width: 0;
}
span {
    text-shadow: 0 0 8px black;
}
</style>
