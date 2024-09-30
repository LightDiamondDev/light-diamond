<script setup lang='ts'>
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode} from '@/helpers'
import {GameEdition, type PostCategory} from '@/types'
import {ref} from 'vue'

import Select from '@/components/elements/Select.vue'
import Input from '@/components/elements/Input.vue'
import Button from '@/components/elements/Button.vue'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
}

const props = defineProps<{
    category?: PostCategory
    goal: String
}>()
const emit = defineEmits(['cancel', 'processed'])

const authStore = useAuthStore()
const toastStore = useToastStore()
const apiUrl = '/api/post-categories'

const category = ref<PostCategory>(props.category || {})
const errors = ref<string[][]>([])
const isProcessing = ref(false)

const categoryEditions = ref([
    { icon: 'icon-diamond', label: 'Любое Издание', value: null },
    { icon: 'icon-bedrock-dev-small', label: 'Бедрок', value: GameEdition.BEDROCK },
    { icon: 'icon-minecraft-materials', label: 'Джава', value: GameEdition.JAVA },
])

function update() {
    errors.value = []
    isProcessing.value = true

    axios.put(`${apiUrl}/${category.value.id}`, category.value!).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastStore.success(`Данные Категории «${category.value.name}» изменены.`)
            emit('processed')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.message) {
                toastStore.error(data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

function add() {
    errors.value = []
    isProcessing.value = true

    axios.post(`${apiUrl}`, category.value).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastStore.success(`Добавлена новая Категория «${category.value.name}».`)
            emit('processed')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.message) {
                toastStore.error(data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

function save() {
    category.value.id ? update() : add()
}
</script>

<template>
    <form action="" class="category flex flex-col items-center w-full" name="category" @submit.prevent="save">
        <fieldset class="flex flex-col items-center w-full">
            <div class="group flex flex-col w-[85%]">

                <span class="subtitle md:text-[14px] text-[12px] my-2" :class="{ 'error': errors['email'] }">Название</span>

                <Input
                    v-model="category.name"
                    class="h-[48px]"
                    id="category-name"
                    placeholder="Название"
                    autocomplete="name"
                    type="name"
                />

                <span
                    class="status md:text-[12px] text-[10px] mt-2"
                    :class="{ 'error': errors['name'] }"
                >
                    {{ errors['name']?.[0] || '&nbsp;' }}
                </span>

                <span class="subtitle md:text-[14px] text-[12px] my-2" :class="{ 'error': errors['slug'] }">Ярлык</span>

                <Input
                    v-model="category.slug"
                    class="h-[48px]"
                    id="category-slug"
                    placeholder="Ярлык"
                    autocomplete="off"
                    type="slug"
                />

                <span
                    class="status md:text-[12px] text-[10px] mt-2"
                    :class="{ 'error': errors['slug'] }"
                >
                    {{ errors['slug']?.[0] || '&nbsp;' }}
                </span>

                <span class="subtitle md:text-[14px] text-[12px] my-2" :class="{ 'error': errors['edition'] }">Издание</span>

                <Select
                    options-classes="ld-primary-background ld-primary-border mt-[-2px]"
                    class="flex self-center w-full"
                    v-model="category.edition"
                    button-classes="ld-primary-border ld-title-font h-[64px]"
                    input-id="category-edition"
                    :options="categoryEditions"
                    option-classes="h-[64px] gap-2 pl-4"
                    option-label-key="label"
                    option-icon-key="icon"
                    option-value-key="value"
                    autocomplete="off"
                    placeholder="Издание"
                />

                <span
                    class="status md:text-[12px] text-[10px] mt-2"
                    :class="{ 'error': errors['edition'] }"
                >
                    {{ errors['edition']?.[0] || '&nbsp;' }}
                </span>
            </div>

            <div class="flex justify-center w-[85%] gap-2 mb-6 mt-2">
                <Button
                    v-if="props.goal === 'editing'"
                    :loading="isProcessing"
                    button-type="submit"
                    label="Сохранить"
                    class="confirm w-full"
                />

                <Button
                    v-if="props.goal === 'adding'"
                    :loading="isProcessing"
                    button-type="submit"
                    label="Добавить"
                    class="confirm w-full"
                />

                <Button
                    label="Отмена"
                    class="cancel w-full"
                    @click="$emit('cancel')"
                />
            </div>
        </fieldset>
    </form>
</template>

<style scoped>

</style>
