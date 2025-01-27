<script setup lang="ts">
import {computed, type PropType, ref, toRaw} from 'vue'
import ItemButton from '@/components/elements/ItemButton.vue'

const props = defineProps({
    currentOption: null,
    buttonClasses: String,
    buttonWrapClasses: String,
    buttonLabelClasses: String,
    disabled: {
        type: Boolean,
        default: false
    },
    editable: {
        type: Boolean,
        default: true
    },
    isCustomOptionItem: {
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
    optionsClasses: String,
    optionClasses: String|(Object as PropType<((args: { option: any }) => String)>),
    placeholder: {
        type: String,
        default: 'Выберите значение'
    }
})

export interface SelectOptionChangeEvent {
    optionIndex: number
    option: any
}

const emit = defineEmits<{
    (e: 'change', event: SelectOptionChangeEvent): void,
    (e: 'show'): void,
}>()
const model = defineModel({default: {}})
const currentOption = computed(() => {
    return props.currentOption ?? props.options!.find(
        (option) => toRaw(model.value) === (props.optionValueKey ? getValueByKeyPath(option, props.optionValueKey) : toRaw(option))
    )
})
const placeholder = ref(props.placeholder)

function getOptionLabel(option) {
    return option && props.optionLabelKey ? getValueByKeyPath(option, props.optionLabelKey) : option.toString()
}

function getOptionIcon(option) {
    return option && props.optionIconKey ? getValueByKeyPath(option, props.optionIconKey) : undefined
}

function getOptionClasses(option: any) {
    return typeof props.optionClasses === 'function'
        ? props.optionClasses(option)
        : props.optionClasses
}

function getValueByKeyPath(obj: object, keyPath: string): any {
    const keys = keyPath.split('.')

    return keys.reduce((currentValue, key) => {
        const index = parseInt(key, 10)
        if (!isNaN(index)) {
            return currentValue?.[index]
        }
        return currentValue?.[key]
    }, obj)
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
        if (option !== currentOption.value) {
            model.value = props.optionValueKey ? option[props.optionValueKey] : option

            const eventArgs: SelectOptionChangeEvent = {
                optionIndex: props.options!.findIndex((tmpOption) => tmpOption === option),
                option: option
            }
            emit('change', eventArgs)
        }
        close()
    }
}
</script>

<template>
    <div
        :class="{ 'disabled': disabled, 'open': isSelectOpen }"
        class="select ld-shadow-text flex flex-col relative min-w-0"
        ref="container"
    >
        <button
            class="select-button flex items-center w-full gap-2"
            :class="buttonClasses"
            :disabled="disabled || !editable"
            type="button"
            @click="toggleSelect"
        >
            <span class="select-span flex items-center w-full min-w-0" :class="getOptionClasses(currentOption)">
                <template v-if="currentOption">
                    <slot name="option-icon" :option="currentOption">
                        <span v-if="optionIconKey" :class="getOptionIcon(currentOption)" class="icon"/>
                    </slot>
                </template>

                <span :class="buttonLabelClasses" class="text overflow-hidden">
                    <template v-if="currentOption">
                        <slot name="option-label" :option="currentOption">
                            <span class="truncate">{{ getOptionLabel(currentOption) }}</span>
                        </slot>
                    </template>
                    <span v-else class="ml-2 whitespace-nowrap opacity-80">{{ placeholder }}</span>
                </span>

                <span v-if="!disabled && editable" class="button-arrow flex justify-center items-center mr-2 ml-auto">
                    <span class="icon icon-down-arrow flex"></span>
                </span>
            </span>
        </button>
        <Transition name="smooth-select-switch">
            <div v-if="isSelectOpen" :class="optionsClasses" class="options flex flex-col w-full absolute">
                <template v-for="option in props.options">
                    <div v-if="isCustomOptionItem" class="option item-button flex" @click="change(option)">
                        <slot name="option-item" :option="option"/>
                    </div>
                    <ItemButton
                        v-else
                        @click="change(option)"
                        :label="getOptionLabel(option)"
                        :icon="getOptionIcon(option)"
                        class="option flex"
                        :class="getOptionClasses(option)"
                    >
                        <template #container>
                            <slot name="option-container" :option="option"/>
                        </template>
                        <template #icon>
                            <slot name="option-icon" :option="option"/>
                        </template>
                        <template #label>
                            <slot name="option-label" :option="option"/>
                        </template>
                    </ItemButton>
                </template>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.button-arrow {
    height: 48px;
    width: 48px;
}

.select .options {
    max-height: 360px;
    overflow-y: auto;
    transition: .2s;
    z-index: 1;
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
}

.select-button:focus-visible {
    background-color: var(--primary-bg-color);
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
        min-width: 48px;
        height: 48px;
    }
}
</style>
