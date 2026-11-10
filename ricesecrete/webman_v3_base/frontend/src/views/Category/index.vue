<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElButton,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElInputNumber
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import {
  getCategoryListApi,
  createCategoryApi,
  updateCategoryApi,
  deleteCategoryApi
} from '@/api/category'

const loading = ref(false)
const list = ref<any[]>([])
const dialogVisible = ref(false)
const dialogTitle = ref('新建分类')
const editingId = ref<number | null>(null)
const form = ref({ name: '', slug: '', sort: 0 })

const resetForm = () => {
  editingId.value = null
  form.value = { name: '', slug: '', sort: 0 }
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getCategoryListApi()
    list.value = res.data || []
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  resetForm()
  dialogTitle.value = '新建分类'
  dialogVisible.value = true
}

const openEdit = (row: any) => {
  editingId.value = row.id
  dialogTitle.value = '编辑分类'
  form.value = { name: row.name, slug: row.slug, sort: row.sort ?? 0 }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!form.value.name.trim() || !form.value.slug.trim()) {
    ElMessage.warning('名称和标识不能为空')
    return
  }
  if (editingId.value) {
    await updateCategoryApi(editingId.value, form.value)
    ElMessage.success('更新成功')
  } else {
    await createCategoryApi(form.value)
    ElMessage.success('创建成功')
  }
  dialogVisible.value = false
  resetForm()
  loadData()
}

const handleDelete = (id: number) => {
  ElMessageBox.confirm('确认删除该分类吗？', '提示', { type: 'warning' }).then(async () => {
    await deleteCategoryApi(id)
    ElMessage.success('删除成功')
    loadData()
  })
}

onMounted(loadData)
</script>

<template>
  <ContentWrap title="文章分类">
    <el-button v-permission="'category:create'" type="primary" class="mb-16px" @click="openCreate">
      新建分类
    </el-button>

    <el-table :data="list" v-loading="loading" border>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="名称" />
      <el-table-column prop="slug" label="标识" />
      <el-table-column prop="sort" label="排序" width="80" />
      <el-table-column label="操作" width="160">
        <template #default="scope">
          <el-button
            v-if="scope?.row"
            v-permission="'category:update'"
            type="primary"
            size="small"
            link
            @click="openEdit(scope.row)"
          >
            编辑
          </el-button>
          <el-button
            v-if="scope?.row"
            v-permission="'category:delete'"
            type="danger"
            size="small"
            link
            @click="handleDelete(scope.row.id)"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="400px" @closed="resetForm">
      <el-form :model="form" label-width="80px">
        <el-form-item label="名称" required><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="标识" required><el-input v-model="form.slug" /></el-form-item>
        <el-form-item label="排序"><el-input-number v-model="form.sort" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </ContentWrap>
</template>
