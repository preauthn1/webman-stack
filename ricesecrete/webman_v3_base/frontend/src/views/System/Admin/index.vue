<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElInput,
  ElButton,
  ElTag,
  ElPagination,
  ElDialog,
  ElForm,
  ElFormItem,
  ElSelect,
  ElOption
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import { getAdminListApi, createAdminApi, updateAdminApi, deleteAdminApi } from '@/api/admin'
import { getRoleListApi } from '@/api/role'

const loading = ref(false)
const list = ref<any[]>([])
const total = ref(0)
const roles = ref<{ id: number; name: string }[]>([])
const queryParams = ref({ page: 1, limit: 10, keyword: '' })

const dialogVisible = ref(false)
const dialogTitle = ref('新建管理员')
const editingId = ref<number | null>(null)
const form = ref({
  username: '',
  password: '',
  nickname: '',
  status: 1,
  role_ids: [] as number[]
})

const resetForm = () => {
  editingId.value = null
  form.value = { username: '', password: '', nickname: '', status: 1, role_ids: [] }
}

const loadRoles = async () => {
  const res = await getRoleListApi({ page: 1, limit: 100 })
  roles.value = res.data?.items ?? []
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getAdminListApi(queryParams.value)
    list.value = res.data.items
    total.value = res.data.total
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  resetForm()
  dialogTitle.value = '新建管理员'
  dialogVisible.value = true
}

const openEdit = (row: any) => {
  editingId.value = row.id
  dialogTitle.value = '编辑管理员'
  form.value = {
    username: row.username,
    password: '',
    nickname: row.nickname || '',
    status: row.status,
    role_ids: (row.roles || []).map((r: any) => r.id)
  }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!editingId.value && (!form.value.username.trim() || !form.value.password.trim())) {
    ElMessage.warning('用户名和密码不能为空')
    return
  }
  try {
    if (editingId.value) {
      await updateAdminApi(editingId.value, {
        nickname: form.value.nickname,
        status: form.value.status,
        role_ids: form.value.role_ids
      })
      ElMessage.success('更新成功')
    } else {
      await createAdminApi(form.value)
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
  ElMessageBox.confirm('确认删除该管理员吗？', '提示', { type: 'warning' }).then(async () => {
    await deleteAdminApi(id)
    ElMessage.success('删除成功')
    loadData()
  })
}

onMounted(() => {
  loadRoles()
  loadData()
})
</script>

<template>
  <ContentWrap title="管理员管理">
    <div class="mb-16px">
      <el-button v-permission="'admin:create'" type="primary" class="mr-12px" @click="openCreate">
        新建管理员
      </el-button>
      <el-input
        v-model="queryParams.keyword"
        placeholder="搜索用户名"
        clearable
        style="width: 200px"
        class="mr-12px"
      />
      <el-button type="primary" @click="loadData">搜索</el-button>
    </div>

    <el-table :data="list" v-loading="loading" border>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="username" label="用户名" />
      <el-table-column prop="nickname" label="昵称" />
      <el-table-column prop="status" label="状态" width="100">
        <template #default="scope">
          <el-tag v-if="scope?.row" :type="scope.row.status === 1 ? 'success' : 'danger'">{{
            scope.row.status === 1 ? '启用' : '禁用'
          }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="角色">
        <template #default="scope">
          <el-tag v-for="role in scope?.row?.roles || []" :key="role.id" class="mr-4px">{{
            role.name
          }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="160">
        <template #default="scope">
          <el-button
            v-if="scope?.row"
            v-permission="'admin:update'"
            type="primary"
            size="small"
            link
            @click="openEdit(scope.row)"
          >
            编辑
          </el-button>
          <el-button
            v-if="scope?.row"
            v-permission="'admin:delete'"
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="480px" @closed="resetForm">
      <el-form :model="form" label-width="80px">
        <el-form-item v-if="!editingId" label="用户名" required>
          <el-input v-model="form.username" placeholder="登录用户名" />
        </el-form-item>
        <el-form-item v-if="!editingId" label="密码" required>
          <el-input v-model="form.password" type="password" placeholder="登录密码" show-password />
        </el-form-item>
        <el-form-item label="昵称">
          <el-input v-model="form.nickname" placeholder="显示昵称" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="角色">
          <el-select v-model="form.role_ids" multiple placeholder="选择角色" style="width: 100%">
            <el-option v-for="role in roles" :key="role.id" :label="role.name" :value="role.id" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </ContentWrap>
</template>
