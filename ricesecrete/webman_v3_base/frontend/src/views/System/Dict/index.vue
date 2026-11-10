<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElInput,
  ElButton,
  ElPagination,
  ElDialog,
  ElForm,
  ElFormItem,
  ElSwitch,
  ElTag
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import {
  getDictListApi,
  createDictApi,
  updateDictApi,
  deleteDictApi,
  getDictItemsApi,
  saveDictItemsApi
} from '@/api/dict'

const loading = ref(false)
const list = ref<any[]>([])
const total = ref(0)
const queryParams = ref({ page: 1, limit: 10, keyword: '' })

const dialogVisible = ref(false)
const itemDialogVisible = ref(false)
const dialogTitle = ref('新增字典')
const editingId = ref<number | null>(null)
const form = ref({ name: '', code: '', status: 1, remark: '' })
const dictItems = ref<any[]>([])
const currentTypeId = ref<number | null>(null)

const loadData = async () => {
  loading.value = true
  try {
    const res = await getDictListApi(queryParams.value)
    list.value = res.data?.items || []
    total.value = res.data?.total || 0
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingId.value = null
  form.value = { name: '', code: '', status: 1, remark: '' }
  dialogTitle.value = '新增字典'
  dialogVisible.value = true
}

const openEdit = (row: any) => {
  editingId.value = row.id
  form.value = {
    name: row.name,
    code: row.code,
    status: Number(row.status) === 1 ? 1 : 0,
    remark: row.remark || ''
  }
  dialogTitle.value = '编辑字典'
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!form.value.name || !form.value.code) {
    ElMessage.warning('名称和编码不能为空')
    return
  }
  if (editingId.value) {
    await updateDictApi(editingId.value, form.value)
    ElMessage.success('更新成功')
  } else {
    await createDictApi(form.value)
    ElMessage.success('创建成功')
  }
  dialogVisible.value = false
  loadData()
}

const handleDelete = async (row: any) => {
  await ElMessageBox.confirm('确定删除该字典类型？', '提示', { type: 'warning' })
  await deleteDictApi(row.id)
  ElMessage.success('删除成功')
  loadData()
}

const openItems = async (row: any) => {
  currentTypeId.value = row.id
  const res = await getDictItemsApi(row.id)
  dictItems.value = (res.data || []).map((item: any, index: number) => ({
    label: item.label,
    value: item.value,
    sort: item.sort ?? index,
    status: Number(item.status) === 1 ? 1 : 0,
    remark: item.remark || ''
  }))
  if (dictItems.value.length === 0) {
    dictItems.value.push({ label: '', value: '', sort: 0, status: 1, remark: '' })
  }
  itemDialogVisible.value = true
}

const addItemRow = () => {
  dictItems.value.push({ label: '', value: '', sort: dictItems.value.length, status: 1, remark: '' })
}

const saveItems = async () => {
  if (!currentTypeId.value) return
  await saveDictItemsApi(currentTypeId.value, dictItems.value)
  ElMessage.success('保存成功')
  itemDialogVisible.value = false
}

onMounted(loadData)
</script>

<template>
  <ContentWrap>
    <div class="mb-4 flex gap-2">
      <ElInput v-model="queryParams.keyword" placeholder="搜索名称/编码" clearable />
      <ElButton type="primary" @click="loadData">搜索</ElButton>
      <ElButton type="success" @click="openCreate">新增字典</ElButton>
    </div>

    <ElTable v-loading="loading" :data="list" border>
      <ElTableColumn prop="id" label="ID" width="80" />
      <ElTableColumn prop="name" label="名称" />
      <ElTableColumn prop="code" label="编码" />
      <ElTableColumn label="状态" width="100">
        <template #default="{ row }">
          <ElTag :type="row.status === 1 ? 'success' : 'info'">
            {{ row.status === 1 ? '启用' : '禁用' }}
          </ElTag>
        </template>
      </ElTableColumn>
      <ElTableColumn prop="remark" label="备注" />
      <ElTableColumn label="操作" width="220">
        <template #default="{ row }">
          <ElButton link type="primary" @click="openEdit(row)">编辑</ElButton>
          <ElButton link type="primary" @click="openItems(row)">字典项</ElButton>
          <ElButton link type="danger" @click="handleDelete(row)">删除</ElButton>
        </template>
      </ElTableColumn>
    </ElTable>

    <div class="mt-4 flex justify-end">
      <ElPagination
        v-model:current-page="queryParams.page"
        v-model:page-size="queryParams.limit"
        :total="total"
        layout="total, prev, pager, next"
        @current-change="loadData"
      />
    </div>

    <ElDialog v-model="dialogVisible" :title="dialogTitle" width="520px">
      <ElForm label-width="90px">
        <ElFormItem label="名称">
          <ElInput v-model="form.name" />
        </ElFormItem>
        <ElFormItem label="编码">
          <ElInput v-model="form.code" :disabled="!!editingId" />
        </ElFormItem>
        <ElFormItem label="状态">
          <ElSwitch v-model="form.status" :active-value="1" :inactive-value="0" />
        </ElFormItem>
        <ElFormItem label="备注">
          <ElInput v-model="form.remark" type="textarea" />
        </ElFormItem>
      </ElForm>
      <template #footer>
        <ElButton @click="dialogVisible = false">取消</ElButton>
        <ElButton type="primary" @click="handleSubmit">确定</ElButton>
      </template>
    </ElDialog>

    <ElDialog v-model="itemDialogVisible" title="字典项管理" width="760px">
      <ElButton class="mb-3" type="primary" @click="addItemRow">新增项</ElButton>
      <ElTable :data="dictItems" border>
        <ElTableColumn label="标签">
          <template #default="{ row }"><ElInput v-model="row.label" /></template>
        </ElTableColumn>
        <ElTableColumn label="值">
          <template #default="{ row }"><ElInput v-model="row.value" /></template>
        </ElTableColumn>
        <ElTableColumn label="排序" width="100">
          <template #default="{ row }"><ElInput v-model.number="row.sort" /></template>
        </ElTableColumn>
        <ElTableColumn label="状态" width="100">
          <template #default="{ row }">
            <ElSwitch v-model="row.status" :active-value="1" :inactive-value="0" />
          </template>
        </ElTableColumn>
      </ElTable>
      <template #footer>
        <ElButton @click="itemDialogVisible = false">取消</ElButton>
        <ElButton type="primary" @click="saveItems">保存</ElButton>
      </template>
    </ElDialog>
  </ContentWrap>
</template>
