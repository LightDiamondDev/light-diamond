<script setup lang='ts'>
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode} from '@/helpers'
import {ref} from 'vue'

import {GameEdition, type PostCategory} from '@/types'

import Button from '@/components/elements/Button.vue'
import CategoryForm from '@/components/dashboard/CategoryForm.vue'
import Category from '@/components/catalog/Category.vue'
import Checkbox from '@/components/elements/Checkbox.vue'
import Dialog from '@/components/elements/Dialog.vue'
import Paginator from '@/components/elements/Paginator.vue'
import ShineButton from '@/components/elements/ShineButton.vue'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
    records?: PostCategory[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const props = defineProps<{
    category?: PostCategory
}>()

const authStore = useAuthStore()
const toastStore = useToastStore()
const apiUrl = '/api/post-categories'
const records = ref<PostCategory[]>([])
const totalRecords = ref(0)

const category = ref<PostCategory>(props.category || {})
const errors = ref<string[][]>([])

const isLoading = ref(false)
const isProcessing = ref(false)
const isAddRecordModal = ref(false)
const isDeleteSelectedModal = ref(false)
const isEditRecordModal = ref(false)
const isDeleteRecordModal = ref(false)

const emit = defineEmits(['cancel', 'processed'])

const loadRecordsData = ref({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: -1
})

const currentRecord = ref<PostCategory | null>(null)

const selectedRecords = ref<PostCategory[]>([])

function loadRecords() {
    isLoading.value = true
    records.value = []

    axios.get(apiUrl, {params: loadRecordsData.value}).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            records.value = data.records!
            totalRecords.value = data.pagination!.total_records
        } else {
            toastStore.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

loadRecords()

// function onChangePage(event: DataTablePageEvent) {
//     loadRecordsData.value.page = event.page + 1
//     loadRecords()
// }
//
// function onSort(event: DataTableSortEvent) {
//     loadRecordsData.value.sort_field = <string>event.sortField!
//     loadRecordsData.value.sort_order = event.sortOrder!
//     loadRecordsData.value.page = 1
//     loadRecords()
// }

function deleteRecord(record: PostCategory) {
    axios.delete(`${apiUrl}/${record.id}`).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            toastStore.success(`Категория «${record.name}» удалена.`)
        } else {
            toastStore.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function deleteSelected() {
    axios.delete(`${apiUrl}`, {params: {ids: selectedRecords.value.map((category) => category.id)}}).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            selectedRecords.value = []
            toastStore.success(`Выбранные Категории удалены.`)
        } else {
            toastStore.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function onEditClick(record: PostCategory) {
    currentRecord.value = {...record}
    isEditRecordModal.value = true
}

function onDeleteClick(record: PostCategory) {
    currentRecord.value = record
    isDeleteRecordModal.value = true
}

function onConfirmDeleteClick() {
    deleteRecord(currentRecord.value!)
    isDeleteRecordModal.value = false
}

function onConfirmDeleteSelectedClick() {
    deleteSelected()
    isDeleteSelectedModal.value = false
}

function onRecordSave() {
    loadRecords()
    isAddRecordModal.value = false
    isEditRecordModal.value = false
}
</script>

<template>
    <div class="section ld-shadow-text flex flex-col h-full">
        <div v-if="authStore.isAdmin" class="flex">
            <ShineButton
                class="confirm ml-2 mt-2" icon="icon-plus"
                @click="isAddRecordModal = true"
            />
            <ShineButton
                class="cancel ml-2 mt-2" icon="icon-trash"
                @click="isDeleteSelectedModal = true"
                :disabled="selectedRecords.length === 0"
            />
        </div>

        <div class="data-table flex flex-col min-h-[100vh] overflow-x-auto">
            <div class="table-header flex text-[var(--primary-color)] lg:text-[14px] text-[12px] min-w-[720px] w-full px-2">
                <div class="row ld-primary-border-bottom flex w-full">
                    <label class="row-item flex items-center h-[48px] min-w-[48px] w-[10%] pl-1" for="">
                        <Checkbox
                            @check="selectedRecords = [...records]"
                            @uncheck="selectedRecords = []"
                        />
                    </label>
                    <span class="row-item flex items-center h-[48px] w-[20%]">Название</span>
                    <span class="row-item flex items-center h-[48px] w-[26%]">Ярлык</span>
                    <span class="row-item flex items-center h-[48px] w-[20%]">Издание</span>
                    <span class="row-item flex items-center h-[48px] w-[12%]">Статья</span>
                    <div v-if="authStore.isAdmin" class="row-item-item flex justify-end w-[12%] pr-1"/>
                </div>
            </div>
            <div class="table-rows flex flex-col lg:text-[12px] text-[10px] min-w-[720px] w-full px-2">
                <div
                    v-for="(record) in records"
                    class="row ld-primary-border-bottom flex w-full"
                    :class="{'transfusion': selectedRecords.includes(record) }"
                >
                    <label class="row-item flex items-center h-[48px] min-w-[48px] w-[10%] pl-1" for="">
                        <Checkbox
                            :checked="selectedRecords.includes(record)"
                            @check="selectedRecords.push(record)"
                            @uncheck="selectedRecords.splice(selectedRecords.indexOf(record), 1)"
                        />
                    </label>
                    <span class="row-item flex items-center h-[48px] w-[20%]">{{ record.name }}</span>
                    <span class="row-item flex items-center h-[48px] w-[26%]">{{ record.slug }}</span>
                    <span
                        class="row-item flex items-center h-[48px] w-[20%]"
                        :class="{
                            'ld-orange-text': record.edition === GameEdition.BEDROCK,
                            'ld-moder-color': record.edition === GameEdition.JAVA,
                            'ld-admin-color': record.edition === null
                        }"
                    >
                        {{
                            record.edition === GameEdition.BEDROCK ?
                            'Бедрок' : record.edition === GameEdition.JAVA ?
                            'Джава' : 'Любое'
                        }}
                    </span>
                    <span
                        class="row-item flex items-center h-[48px] w-[12%]"
                        :class="{ 'ld-moder-color': record.is_article }"
                    >
                        {{ record.is_article ? 'Да' : 'Нет' }}
                    </span>
                    <div v-if="authStore.isAdmin" class="row-item-item flex justify-end w-[12%]">
                        <button class="flex justify-center items-center h-full lg:min-w-[48px] min-w-[36px] border-0" @click="onEditClick(record)">
                            <span class="icon-small-pencil icon flex" v-tooltip.top="'Редактировать'"/>
                        </button>
                        <button class="flex justify-center items-center h-full lg:min-w-[48px] min-w-[36px] border-0" @click="onDeleteClick(record)">
                            <span class="icon-trash icon flex" v-tooltip.top="'Удалить'"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Dialog
            v-model:visible="isAddRecordModal"
            class="category-adding-form"
            dialog-classes="md:top-0 top-[-200px]"
            title="Добавление"
        >
            <CategoryForm
                @cancel="isAddRecordModal = false"
                class="sm:min-w-[390px] max-w-[390px]"
                goal="adding"
                @processed="onRecordSave"
            />
        </Dialog>


        <Dialog
            v-model:visible="isEditRecordModal"
            class="category-editing-form"
            dialog-classes="md:top-0 top-[-200px]"
            title="Редактирование"
        >
            <CategoryForm
                @cancel="isEditRecordModal = false"
                class="sm:min-w-[390px] max-w-[390px]"
                :category="currentRecord!"
                goal="editing"
                @processed="onRecordSave"
            />
        </Dialog>

        <Dialog
            v-model:visible="isDeleteSelectedModal"
            class="category-delete-form"
            dialog-classes="md:top-0 top-[-200px]"
            title="Удаление"
        >
            <form action="" class="register flex flex-col items-center sm:min-w-[390px] max-w-[390px]" name="register">
                <p class="subtitle md:text-[14px] text-[12px] text-center mb-4">
                    Вы действительно хотите удалить выбранных Пользователей [всего {{ selectedRecords.length }}]?
                </p>

                <div class="flex justify-center w-[85%] gap-2 mb-6">
                    <Button
                        button-type="submit"
                        label="Удалить"
                        class="delete min-w-[140px]"
                        @click.prevent="onConfirmDeleteSelectedClick"
                    />

                    <Button
                        button-type="submit"
                        label="Отмена"
                        class="cancel min-w-[140px]"
                        @click.prevent="isDeleteSelectedModal = false"
                    />
                </div>

            </form>
        </Dialog>

        <Dialog
            v-model:visible="isDeleteRecordModal"
            class="category-form"
            dialog-classes="md:top-0 top-[-200px]"
            title="Удаление"
        >
            <form action="" class="register flex flex-col items-center sm:min-w-[390px] max-w-[390px]" name="register">
                <p class="subtitle md:text-[14px] text-[12px] text-center mb-4">
                    Вы действительно хотите удалить Категорию «{{ currentRecord!.name }}»?
                </p>

                <div class="flex justify-center w-[85%] gap-2 mb-6">
                    <Button
                        button-type="submit"
                        label="Удалить"
                        class="delete min-w-[140px]"
                        @click.prevent="onConfirmDeleteClick"
                    />

                    <Button
                        button-type="submit"
                        label="Отмена"
                        class="cancel min-w-[140px]"
                        @click.prevent="isDeleteRecordModal = false"
                    />
                </div>

            </form>
        </Dialog>
    </div>
</template>

<style scoped>
.category-adding-form {
    top: 0;
}
</style>
