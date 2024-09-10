<script setup lang="ts">
const props = defineProps({
    disabled: {
        type: Boolean,
        default: false
    },
    editable: {
        type: Boolean,
        default: false
    },
    fileFormat: {
        type: String,
        default: ''
    },
    fileName: {
        type: String,
        default: 'Загруженный Файл'
    },
    fileSize: {
        type: String,
        default: ''
    },
    id: String
})

const model = defineModel()

function deleteFile() {

}
</script>

<template>
    <div
        :class="{ 'disabled': disabled }"
        class="loaded-file-background ld-primary-background ld-shadow-text flex"
        ref="container"
    >
        <div class="loaded-file flex w-full">
            <label class="loaded-file-label flex justify-between items-center w-full" :for="id">
                <span class="loaded-file-span flex items-center w-full gap-4 pl-4">
                    <span class="icon-download icon min-w-[2rem]"/>
                    <span class="text flex flex-col">
                        <span class="file-name flex duration-200">{{ fileName }}</span>
                        <span class="title-font flex opacity-80">{{ fileFormat + ' — ' + fileSize }}</span>
                    </span>
                    <input class="hidden" :disabled="disabled" :id="id" type="file">
                </span>
            </label>
            <button class="button-cross flex justify-center items-center" @click="deleteFile">
                <span class="icon-cross icon flex"/>
            </button>
        </div>
    </div>
</template>

<style scoped>
.button-cross {
    height: 72px;
    width: 72px;
}

.button-cross .icon {
    transition: .2s;
}

.loaded-file.disabled {
    opacity: .8;
}

.loaded-file-label {
    overflow: hidden;
    cursor: pointer;
    height: 72px;
}

.loaded-file-label .icon {
    height: 32px;
    width: 32px;
}

.loaded-file:hover .icon-download {
    animation: icon-trigger-up-animation .3s ease;
}

.loaded-file-label .text {
    color: var(--primary-text-color);
    font-size: 14px;
}

.loaded-file-label .text span {
    line-height: 1.5;
}

.loaded-file:hover .text .file-name {
    color: var(--hover-text-color);
}

/* =============== [ Медиа-Запрос { ?px < 1024px } ] =============== */

@media screen and (max-width: 1023px) {
    .button-cross {
        min-width: 64px;
        height: 64px;
    }

    .loaded-file-label {
        height: 64px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .loaded-file-label .text {
        font-size: 12px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .loaded-file-label .text {
        font-size: 10px;
    }
}
</style>
