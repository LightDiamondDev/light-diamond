<script setup lang='ts'>
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode} from '@/helpers'
import {ref} from 'vue'

import {type PostCategory, type User, UserRole} from '@/types'

import Button from '@/components/elements/Button.vue'
import Dialog from '@/components/elements/Dialog.vue'
import Paginator from '@/components/elements/Paginator.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import Input from '@/components/elements/Input.vue'
import UserForm from '@/components/dashboard/UserForm.vue'
import Checkbox from '@/components/elements/Checkbox.vue'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
    records?: User[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const authStore = useAuthStore()
const toastStore = useToastStore()
const apiUrl = '/api/users'
const records = ref<User[]>([])
const totalRecords = ref(0)

const isLoading = ref(false)
const isAddRecordModal = ref(false)
const isDeleteSelectedModal = ref(false)
const isEditRecordModal = ref(false)
const isDeleteRecordModal = ref(false)

const loadRecordsData = ref({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: -1
})

const currentRecord = ref<User | null>(null)

const userRoles = ref([
    {label: 'Пользователь', value: UserRole.USER},
    {label: 'Модератор', value: UserRole.MODERATOR},
    {label: 'Администратор', value: UserRole.ADMIN},
])
const selectedRecords = ref<User[]>([])

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

function getUserRoleLabel(role: UserRole) {
    return userRoles.value.find((item) => item.value === role)?.label || ''
}

function onChangePage(event: DataTablePageEvent) {
    loadRecordsData.value.page = event.page + 1
    loadRecords()
}

function onSort(event: DataTableSortEvent) {
    loadRecordsData.value.sort_field = <string>event.sortField!
    loadRecordsData.value.sort_order = event.sortOrder!
    loadRecordsData.value.page = 1
    loadRecords()
}

function deleteRecord(record: User) {
    axios.delete(`${apiUrl}/${record.id}`).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            toastStore.success(`Пользователь ${record.username} удалён.`)
        } else {
            toastStore.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function deleteSelected() {
    axios.delete(`${apiUrl}`, {params: {ids: selectedRecords.value.map((user) => user.id)}}).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            selectedRecords.value = []
            toastStore.success(`Выбранные Пользователи удалены.`)
        } else {
            toastStore.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function onEditClick(record: User) {
    currentRecord.value = {...record}
    isEditRecordModal.value = true
}

function onDeleteClick(record: User) {
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

// import DataTable, {type DataTablePageEvent, type DataTableSortEvent} from 'primevue/datatable'
</script>

<template>
    <div class="section ld-shadow-text flex flex-col h-full">
        <div v-if="authStore.isAdmin" class="flex">
            <ShineButton
                class="cancel ml-2 mt-2" icon="icon-trash"
                @click="() => isDeleteSelectedModal = true"
                :disabled="selectedRecords.length === 0"
            />
        </div>

        <div class="data-table flex flex-col overflow-x-auto">
            <div class="table-header flex text-[var(--primary-color)] lg:text-[14px] text-[12px] min-w-[720px] w-full px-2">
                <div class="row ld-primary-border-bottom flex w-full">
                    <label class="row-item flex items-center h-[48px] min-w-[48px] w-[8%] pl-1" for="">
                        <Checkbox/>
                    </label>
                    <span class="row-item flex items-center h-[48px] w-[23%]">Никнейм</span>
                    <span class="row-item flex items-center h-[48px] w-[35%]">E-Mail</span>
                    <span class="row-item flex items-center h-[48px] w-[20%]">Роль</span>
                    <div v-if="authStore.isAdmin" class="row-item-item flex justify-end w-[12%] pr-1"/>
                </div>
            </div>
            <div class="table-rows flex flex-col lg:text-[12px] text-[10px] min-w-[720px] w-full px-2">
                <div v-for="(record) in records" class="row ld-primary-border-bottom flex w-full">
                    <label class="row-item flex items-center h-[48px] min-w-[48px] w-[8%] pl-1" for="">
                        <Checkbox
                            @check="selectedRecords.push(record)"
                            @uncheck="selectedRecords.splice(selectedRecords.indexOf(record), 1)"
                        />
                    </label>
                    <span class="row-item flex items-center h-[48px] w-[23%]">{{ record.username }}</span>
                    <span class="row-item flex items-center h-[48px] w-[35%]">{{ record.email }}</span>
                    <span
                        class="row-item flex items-center h-[48px] w-[20%]"
                        :class="{'ld-admin-color': record.role === 'ADMIN', 'ld-moder-color': record.role === 'MODERATOR'}"
                    >
                        {{ record.role === 'USER' ? 'Пользователь' : record.role === 'MODERATOR' ? 'Модератор' : 'Администратор' }}
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

        <!--
            :rows="loadRequestData.per_page"
            :totalRecords="totalRecords"
            :class="{'hidden': !isLoading && postVersions.length === 0}"
            @page="onPageChange"
            -->
        <Paginator
            class="h-[48px] gap-6"
        />

        <!--
            :value="isLoading ? new Array(3) : records"
            lazy
            paginator
            removable-sort
            v-model:selection="selectedRecords"
            selectionMode="multiple"
            :rows="loadRecordsData.per_page"
            :total-records="totalRecords"
            @page="onChangePage($event)"
            @sort="onSort($event)"
        -->

        <Dialog
            v-model:visible="isEditRecordModal"
            title="Редактирование"
            class="user-form"
            style="top: 0;"
        >
            <UserForm :user="currentRecord" @cancel="isEditRecordModal = false" @processed="onRecordSave"/>
        </Dialog>

        <Dialog
            v-model:visible="isDeleteSelectedModal"
            class="user-form"
            title="Удаление"
            style="top: 0;"
        >
            <form action="" class="register flex flex-col items-center max-w-[450px]" name="register">
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
            class="user-form"
            title="Удаление"
            style="top: 0;"
        >
            <form action="" class="register flex flex-col items-center max-w-[450px]" name="register">
                <p class="subtitle md:text-[14px] text-[12px] text-center mb-4">
                    Вы действительно хотите удалить Пользователя {{ currentRecord!.username }}?
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
.row:hover {
    background-color: rgba(255, 255, 255, .1);
}
</style>
