<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
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
    optionLabelKey: String,
    optionIconKey: String,
    optionValueKey: String,
    options: {
        type: Object as PropType<any[]>,
        required: true
    },
    optionClasses: String,
    placeholder: {
        type: String,
        default: 'Выберите значение'
    }
})

const emit = defineEmits(['change', 'show'])
const model = defineModel()
const currentOption = computed(() => {
    return props.options!.find(
        (option) => model.value === (props.optionValueKey ? option[props.optionValueKey] : option)
    )
})
const placeholder = ref(props.placeholder)

function getOptionLabel(option) {
    return option && props.optionLabelKey ? option[props.optionLabelKey] : option
}

function getOptionIcon(option) {
    return option && props.optionIconKey ? option[props.optionIconKey] : undefined
}

const isSelfMouseDown = ref(false)
const container = ref<HTMLElement>()
const isSelectOpen = ref(false)

function onDocumentMouseUp(event: MouseEvent) {
    if (!(event.target instanceof HTMLElement)) {
        return
    }

    const isContainerMouseUp = container.value?.contains(event.target)
    const isOutsideClicked = !isSelfMouseDown.value && !isContainerMouseUp

    if (isOutsideClicked) {
        close()
    }
    isSelfMouseDown.value = false
}

function onSelfMouseDown() {
    isSelfMouseDown.value = true
}

function open() {
    isSelectOpen.value = true

    container.value!.addEventListener('mousedown', onSelfMouseDown)
    addEventListener('mouseup', onDocumentMouseUp)
}

function close() {
    isSelectOpen.value = false

    container.value!.removeEventListener('mousedown', onSelfMouseDown)
    removeEventListener('mouseup', onDocumentMouseUp)
}

function toggleSelect() {
    if (isSelectOpen.value) {
        close()
    } else {
        open()
        emit('show')
    }
}

function change(option: any) {
    if (isSelectOpen.value) {
        const newModelValue = props.optionValueKey ? option[props.optionValueKey] : option
        if (newModelValue !== model.value) {
            model.value = newModelValue
            emit('change', option)
        }
        close()
    }
}
</script>

<template>
    <div
        :class="{ 'disabled': disabled, 'open': isSelectOpen }"
        class="select ld-primary-background ld-shadow-text flex flex-col relative"
        ref="container"
    >
        <button
            :class="{ 'cursor-default': disabled }"
            class="select-button flex justify-between items-center"
            :disabled="disabled"
            type="button"
            @click="toggleSelect"
        >
            <span class="select-span flex items-center w-full gap-4" :class="optionClasses">
                <template v-if="currentOption">
                    <slot name="option-icon" :option="currentOption">
                        <span v-if="optionIconKey" :class="getOptionIcon(currentOption)" class="icon"></span>
                    </slot>
                </template>

                <span class="text">
                    <span v-if="currentOption">{{ getOptionLabel(currentOption) }}</span>
                    <span v-else class="opacity-80 ml-2">{{ placeholder }}</span>
                </span>
            </span>
            <span v-if="!disabled" class="button-arrow flex justify-center items-center">
                <span class="icon icon-down-arrow flex"></span>
            </span>
        </button>
        <Transition name="smooth-select-switch">
            <div v-if="isSelectOpen" class="options ld-primary-background flex flex-col w-full absolute">
                <template v-for="option in props.options">
                    <ItemButton
                        @click="change(option)"
                        :label="getOptionLabel(option)"
                        :icon="getOptionIcon(option)"
                        class="option flex"
                        :class="optionClasses"
                    >
                        <template #icon>
                            <slot name="option-icon" :option="option"/>
                        </template>
                    </ItemButton>
                </template>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.select-button.cursor-default {
    cursor: default;
}
.button-arrow {
    height: 72px;
    width: 72px;
}

.select .options {
    max-height: 360px;
    overflow-y: auto;
    transition: .2s;
    z-index: 1;
    top: 72px;
}

.select.disabled {
    opacity: .8;
}

.select.open .option {
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

.select.open .button-arrow .icon {
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
