<script setup lang="ts">
import {type PropType, ref} from 'vue'
import ItemButton from '@/components/elements/ItemButton.vue'

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false
    },
    editable: {
        type: Boolean,
        default: false
    },
    icon: {
        type: String,
        default: 'icon-down-arrow'
    },
    optionLabelKey:  String,
    optionIconKey:  String,
    options: {
        type: Object as PropType<any[]>,
        required: true
    },
    placeholder: {
        type: String,
        default: 'Выберите значение'
    }
})

const emit = defineEmits(['change'])
const model = defineModel<any>()

const isSelfMouseDown = ref(false)
const container = ref<HTMLElement>()
const isSelectOpened = ref(false)

function onDocumentMouseUp(event: MouseEvent) {
    if (!(event.target instanceof HTMLElement)) {
        return
    }

    const isContainerMouseUp = container.value?.contains(event.target)
    const isOutsideClicked = !isSelfMouseDown.value && !isContainerMouseUp

    if (isOutsideClicked) {
        isSelectOpened.value = !isSelectOpened.value
    }
    isSelfMouseDown.value = false
}

function toggleSelect() {
    isSelectOpened.value = !isSelectOpened.value
    if (isSelectOpened.value) {
        addEventListener('mouseup', onDocumentMouseUp)
    } else {
        removeEventListener('mouseup', onDocumentMouseUp)
    }
}

function select(option: any) {
    model.value = option
    emit('change', option)
    toggleSelect()
}
</script>

<template>
    <div
        :class="{ 'disabled': disabled, 'opened': isSelectOpened }"
        class="select flex flex-col relative"
        @mousedown="isSelfMouseDown = true"
        ref="container"
    >
        <button
            class="select-button flex justify-between items-center"
            @click="toggleSelect"
            :disabled="disabled"
            type="button"
        >
            <span class="flex items-center w-full gap-4 pl-6">
                <span v-if="model !== undefined" :class="optionIconKey ? model[optionIconKey] : model" class="icon" />
                <span class="text">
                    <span v-if="model === undefined" class="opacity-80">{{ placeholder }}</span>
                    <span v-else>{{ optionLabelKey ? model[optionLabelKey] : model }}</span>
                </span>
            </span>
            <span class="button-arrow flex justify-center items-center">
                <span class="icon icon-down-arrow flex"></span>
            </span>
        </button>
        <Transition name="smooth-select-switch">
            <div
                v-if="isSelectOpened"
                class="options flex flex-col w-full absolute"
            >
                <template v-for="option in props.options">

                    <ItemButton
                        @click="select(option)"
                        :text="optionLabelKey ? option[optionLabelKey] : option"
                        :icon="optionIconKey ? option[optionIconKey] : option"
                        class="option flex pl-6"
                    >
                        <template #icon>
                            <slot name="option-icon"/>
                        </template>
                    </ItemButton>

                </template>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.button-arrow {
    height: 72px;
    width: 72px;
}
.select .options {
    transition: .2s;
    z-index: 1;
    top: 72px;
}
.select.disabled {
    opacity: .8;
}
.select.opened .option {
    opacity: 1;
}
.select-button {
    overflow: hidden;
    cursor: pointer;
    height: 72px;
}
.select-button .icon {
    height: 32px;
    width: 32px;
}
.button-arrow .icon {
    transition: .2s;
}
.select.opened .button-arrow .icon {
    transform: rotate(180deg);
}

/* =============== [ Анимации ] =============== */

.smooth-select-switch-enter-active {
    transition: .8s ease;
}

.smooth-select-switch-enter-from {
    transform: translateY(-20%);
    transition: 1s;
    opacity: 0;
}

.smooth-select-switch-leave-to {
    transform: translateY(-20%);
    transition: 1s;
    opacity: 0;
}

/* =============== [ Медиа-Запрос { ?px < 1024px } ] =============== */

@media screen and (max-width: 1023px) {
    .button-arrow {
        min-width: 64px;
        height: 64px;
    }
    .select .options {
        top: 64px;
    }
    .select-button {
        height: 64px;
    }
}
</style>
