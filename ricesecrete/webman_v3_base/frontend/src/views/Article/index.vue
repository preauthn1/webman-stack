<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElInput,
  ElSelect,
  ElOption,
  ElButton,
  ElTag,
  ElPagination,
  ElDialog,
  ElForm,
  ElFormItem
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import { Editor } from '@/components/Editor'
import { useUserStore } from '@/store/modules/user'
import { PATH_URL } from '@/axios/service'
import {
  getArticleListApi,
  getArticleDetailApi,
  createArticleApi,
  updateArticleApi,
  deleteArticleApi
} from '@/api/article'
import { getCategoryListApi } from '@/api/category'
import type { Article, ArticleListParams } from '@/types/article'

const loading = ref(false)
const list = ref<Article[]>([])
const total = ref(0)
const categories = ref<{ id: number; name: string }[]>([])
const queryParams = ref<ArticleListParams>({ page: 1, limit: 10, keyword: '', status: '' })

const userStore = useUserStore()
const importInputRef = ref<HTMLInputElement | null>(null)
const dialogVisible = ref(false)
const dialogTitle = ref('新建文章')
const editingId = ref<number | null>(null)
const form = ref({
  title: '',
  summary: '',
  content: '',
  category_id: null as number | null,
  status: 0 as 0 | 1
})

const resetForm = () => {
  editingId.value = null
  form.value = { title: '', summary: '', content: '', category_id: null, status: 0 }
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getArticleListApi(queryParams.value)
    list.value = res.data?.items ?? []
    total.value = res.data?.total ?? 0
  } catch {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  const res = await getCategoryListApi()
  categories.value = res.data || []
}

const openCreate = () => {
  resetForm()
  dialogTitle.value = '新建文章'
  dialogVisible.value = true
}

const openEdit = async (row: Article) => {
  editingId.value = row.id
  dialogTitle.value = '编辑文章'
  try {
    const res = await getArticleDetailApi(row.id)
    const article = res.data
    if (!article) return
    form.value = {
      title: article.title,
      summary: article.summary || '',
      content: article.content || '',
      category_id: article.category_id,
      status: article.status
    }
    dialogVisible.value = true
  } catch {
    ElMessage.error('加载文章详情失败')
  }
}

const handleSubmit = async () => {
  if (!form.value.title.trim()) {
    ElMessage.warning('标题不能为空')
    return
  }
  try {
    if (editingId.value) {
      await updateArticleApi(editingId.value, form.value)
      ElMessage.success('更新成功')
    } else {
      await createArticleApi(form.value)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    resetForm()
    loadData()
  } catch {
    ElMessage.error(editingId.value ? '更新失败' : '创建失败')
  }
}

const handleDelete = (id: number) => {
  ElMessageBox.confirm('确认删除该文章吗？', '提示', { type: 'warning' }).then(async () => {
    await deleteArticleApi(id)
    ElMessage.success('删除成功')
    loadData()
  })
}

const handleExport = async () => {
  const response = await fetch(`${PATH_URL}/admin/articles/export`, {
    headers: {
      Authorization: `Bearer ${userStore.getToken}`
    }
  })
  if (!response.ok) {
    ElMessage.error('导出失败')
    return
  }
  const blob = await response.blob()
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `articles_${Date.now()}.csv`
  link.click()
  URL.revokeObjectURL(url)
}

const triggerImport = () => {
  importInputRef.value?.click()
}

const handleImport = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('file', file)
  const response = await fetch(`${PATH_URL}/admin/articles/import`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${userStore.getToken}`
    },
    body: formData
  })
  const result = await response.json()
  if (result.code === 0) {
    ElMessage.success(result.msg || '导入成功')
    loadData()
  } else {
    ElMessage.error(result.msg || '导入失败')
  }
  input.value = ''
}

onMounted(() => {
  loadCategories()
  loadData()
})
</script>

<template>
  <ContentWrap title="文章管理">
    <div class="mb-16px flex gap-12px">
      <el-button v-permission="'article:create'" type="primary" @click="openCreate"
        >新建文章</el-button
      >
      <el-button v-permission="'article:export'" @click="handleExport">导出 CSV</el-button>
      <el-button v-permission="'article:import'" @click="triggerImport">导入 CSV</el-button>
      <input ref="importInputRef" type="file" accept=".csv" class="hidden" @change="handleImport" />
      <el-input
        v-model="queryParams.keyword"
        placeholder="搜索标题"
        clearable
        style="width: 200px"
      />
      <el-select v-model="queryParams.status" placeholder="状态" clearable style="width: 120px">
        <el-option label="已发布" :value="1" />
        <el-option label="草稿" :value="0" />
      </el-select>
      <el-button type="primary" @click="loadData">搜索</el-button>
    </div>

    <el-table :data="list" v-loading="loading" border>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="title" label="标题" min-width="200" />
      <el-table-column label="分类" width="120">
        <template #default="scope">
          {{ scope?.row?.category?.name || '-' }}
        </template>
      </el-table-column>
      <el-table-column prop="status" label="状态" width="100">
        <template #default="scope">
          <el-tag v-if="scope?.row" :type="scope.row.status === 1 ? 'success' : 'info'">
            {{ scope.row.status === 1 ? '已发布' : '草稿' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="view_count" label="阅读量" width="100" />
      <el-table-column prop="created_at" label="创建时间" width="180" />
      <el-table-column label="操作" width="160" fixed="right">
        <template #default="scope">
          <el-button
            v-if="scope?.row"
            v-permission="'article:update'"
            type="primary"
            size="small"
            link
            @click="openEdit(scope.row)"
          >
            编辑
          </el-button>
          <el-button
            v-if="scope?.row"
            v-permission="'article:delete'"
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

    <el-pagination
      class="mt-16px"
      :total="total"
      v-model:page-size="queryParams.limit"
      v-model:current-page="queryParams.page"
      layout="total, prev, pager, next"
      @change="loadData"
    />

    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="900px"
      destroy-on-close
      @closed="resetForm"
    >
      <el-form :model="form" label-width="80px">
        <el-form-item label="标题" required>
          <el-input v-model="form.title" placeholder="请输入标题" />
        </el-form-item>
        <el-form-item label="分类">
          <el-select
            v-model="form.category_id"
            placeholder="选择分类"
            clearable
            style="width: 100%"
          >
            <el-option
              v-for="item in categories"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="草稿" :value="0" />
            <el-option label="已发布" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="摘要">
          <el-input v-model="form.summary" type="textarea" :rows="2" placeholder="文章摘要" />
        </el-form-item>
        <el-form-item label="内容">
          <Editor
            v-if="dialogVisible"
            v-model="form.content"
            :editor-id="`article-editor-${editingId ?? 'new'}`"
            height="400px"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </ContentWrap>
</template>
