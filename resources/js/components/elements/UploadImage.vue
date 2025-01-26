<script setup lang="ts">
import {ref} from 'vue'
import {useToastStore} from '@/stores/toast'
import {withCaptcha, getErrorMessageByCode} from '@/helpers'

import axios, {type AxiosError} from 'axios'

import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

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
    imageSrc: String,
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
    uploadingItem: {
        type: String,
        default: 'item-diamond'
    }
})

const emit = defineEmits<{
    (e: 'upload', imagePath: string, imageUrl: string): void
}>()

const model = defineModel<string>({default: ''})
const toastStore = useToastStore()

const allowedImageFormats = props.allowedGif ? ['PNG', 'JPG', 'JPEG', 'GIF'] : ['PNG', 'JPG', 'JPEG']
const allowedFormats = allowedImageFormats.map((format) => 'image/' + format.toLowerCase())
const maxSizeInMegabytes = props.maxSizeInMegabytes
const minHeight = props.minHeight
const minWidth = props.minWidth

const isDraggingOver = ref(false)
const isUploading = ref(false)

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

        withCaptcha(() => {
            isUploading.value = true
            const formData = new FormData()
            formData.append('image', file)
            axios.post('/api/upload-image', formData).then((response) => {
                if (response.data.success) {
                    const imagePath = response.data.image_path
                    const imageUrl = response.data.image_url
                    emit('upload', imagePath, imageUrl)
                    isUploading.value = false
                } else {
                    if (response.data.errors) {
                        toastStore.error(response.data.errors['image'][0])
                    }
                    isUploading.value = false
                }
            }).catch((error: AxiosError) => {
                toastStore.error(getErrorMessageByCode(error.response!.status))
                isUploading.value = false
            })
        })
    }
    tempImg.src = URL.createObjectURL(file)
}
</script>

<template>
    <label
        :class="{
            'disabled': !editable, 'cursor-pointer': editable, 'dragover': isDraggingOver,
             'loaded': imageSrc , 'uploading': isUploading
        }"
        class="upload-image-container flex justify-center items-center locked"
        :style="'background-image: url(' + imageSrc + ')'"
        @dragenter.prevent="onDragEnter"
        @dragleave="onDragLeave"
        @dragover.prevent
        @drop.prevent="onDrop"
        :for="id"
    >
        <em class="upload-image-info-wrap flex justify-center items-center h-full w-full" :class="{ 'loaded': imageSrc }">
            <span class="upload-image-info flex flex-col items-center gap-2 p-4">
                <span class="upload-image-heading flex gap-2">
                    <span :class="icon" class="icon relative"/>
                    <span class="head-font md:text-[1.2rem] text-center duration-200">{{ title }}</span>
                </span>
                <span class="text-center">Минимальное разрешение — {{ minWidth }} × {{ minHeight }}</span>
                <span class="text-center">Максимальный размер — {{ maxSizeInMegabytes }} Мб</span>
                <span class="text-center">{{ allowedImageFormats.join(' / ') }}</span>
                <ProcessingMovingItems
                    v-if="isUploading"
                    :item="uploadingItem"
                    height="4rem"
                    width="4rem"
                />
                <input
                    :disabled="!editable"
                    @change="onChange"
                    :accept="allowedFormats"
                    type="file"
                    :id="id"
                />
            </span>
        </em>
    </label>
</template>

<style scoped>
.upload-image-container {
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    aspect-ratio: 16/9;
    object-fit: cover;
    width: 100%;
}

.upload-image-container:active .icon,
.upload-image-container:hover .icon,
.upload-image-container.dragover .icon {
    animation: levitation-icon-animation infinite 1s ease;
}

.upload-image-info-wrap.loaded {
    background: radial-gradient(
        rgba(0, 0, 0, .7),
        rgba(0, 0, 0, .6),
        rgba(0, 0, 0, .1),
        rgba(0, 0, 0, .1),
        rgba(0, 0, 0, .1)
    );
    box-shadow: 0 0 2em rgba(0, 0, 0, .6);
    background-size: 100% 100%;
    pointer-events: none;
}

em {
    transition: .5s;
}

em span {
    text-shadow: none;
}

em:not(.loaded) {
    color: var(--secondary-text-color);
}

em.loaded span {
    text-shadow: 0 0 8px black;
}

em.loaded {
    opacity: 0;
}

em.loaded .upload-image-heading {
    color: var(--hover-text-color);
}

.upload-image-container:hover em.loaded:not(:hover) {
    opacity: 1;
}

.upload-image-container.loaded:not(.dragover) {
    outline: 0;
}

.upload-image-container.uploading .upload-image-info-wrap,
.upload-image-container.dragover .upload-image-info-wrap {
    opacity: 1;
}

.upload-image-container.disabled em {
    display: none;
}

input {
    height: 0;
    width: 0;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    em span {
        font-size: .9rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 425px } ] =============== */

@media screen and (max-width: 425px) {
    em span {
        font-size: .7rem;
    }
}
</style>
