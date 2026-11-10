<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElButton,
  ElPagination,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElTree,
  ElTag
} from 'element-plus'
import type { ElTree as ElTreeType } from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import {
  getRoleListApi,
  createRoleApi,
  updateRoleApi,
  deleteRoleApi,
  getRolePermissionsApi,
  assignRolePermissionsApi
} from '@/api/role'
import { getPermissionListApi } from '@/api/permission'

interface PermissionNode {
  id: number
  name: string
  slug: string
  type: string
  children?: PermissionNode[]
}

const loading = ref(false)
const list = ref<any[]>([])
const total = ref(0)
const queryParams = ref({ page: 1, limit: 10, keyword: '' })

const dialogVisible = ref(false)
const dialogTitle = ref('新建角色')
const editingId = ref<number | null>(null)
const form = ref({ name: '', slug: '', description: '', data_scope: 'self' })

const assignDialogVisible = ref(false)
const assignLoading = ref(false)
const assignSubmitting = ref(false)
const assigningRole = ref<{ id: number; name: string; slug: string } | null>(null)
const permissionTree = ref<PermissionNode[]>([])
const permissionTreeRef = ref<InstanceType<typeof ElTreeType>>()

const resetForm = () => {
  editingId.value = null
  form.value = { name: '', slug: '', description: '', data_scope: 'self' }
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getRoleListApi(queryParams.value)
    list.value = res.data.items
    total.value = res.data.total
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  resetForm()
  dialogTitle.value = '新建角色'
  dialogVisible.value = true
}

const openEdit = (row: any) => {
  editingId.value = row.id
  dialogTitle.value = '编辑角色'
  form.value = {
    name: row.name,
    slug: row.slug,
    description: row.description || '',
    data_scope: row.data_scope || 'self'
  }
  dialogVisible.value = true
}

const openAssign = async (row: any) => {
  if (row.slug === 'super_admin') {
    ElMessage.warning('超级管理员拥有全部权限，无需分配')
    return
  }

  assigningRole.value = { id: row.id, name: row.name, slug: row.slug }
  assignDialogVisible.value = true
  assignLoading.value = true

  try {
    const [permissionRes, rolePermRes] = await Promise.all([
      permissionTree.value.length ? Promise.resolve(null) : getPermissionListApi(),
      getRolePermissionsApi(row.id)
    ])

    if (permissionRes) {
      permissionTree.value = permissionRes.data
    }

    await nextTick()
    permissionTreeRef.value?.setCheckedKeys(rolePermRes.data.permission_ids || [], false)
  } catch {
    ElMessage.error('加载权限数据失败')
    assignDialogVisible.value = false
    assigningRole.value = null
  } finally {
    assignLoading.value = false
  }
}

const resetAssign = () => {
  assigningRole.value = null
  permissionTreeRef.value?.setCheckedKeys([], false)
}

const handleAssignSubmit = async () => {
  if (!assigningRole.value) {
    return
  }

  const checkedKeys = permissionTreeRef.value?.getCheckedKeys(false) || []
  const halfCheckedKeys = permissionTreeRef.value?.getHalfCheckedKeys() || []
  const permissionIds = [...new Set([...checkedKeys, ...halfCheckedKeys])] as number[]

  assignSubmitting.value = true
  try {
    await assignRolePermissionsApi(assigningRole.value.id, permissionIds)
    ElMessage.success('授权成功')
    assignDialogVisible.value = false
    resetAssign()
  } catch {
    ElMessage.error('授权失败')
  } finally {
    assignSubmitting.value = false
  }
}

const handleSubmit = async () => {
  if (!form.value.name.trim()) {
    ElMessage.warning('角色名称不能为空')
    return
  }
  if (!editingId.value && !form.value.slug.trim()) {
    ElMessage.warning('角色标识不能为空')
    return
  }
  try {
    if (editingId.value) {
      await updateRoleApi(editingId.value, {
        name: form.value.name,
        description: form.value.description,
        data_scope: form.value.data_scope
      })
      ElMessage.success('更新成功')
    } else {
      await createRoleApi(form.value)
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
  ElMessageBox.confirm('确认删除该角色吗？', '提示', { type: 'warning' }).then(async () => {
    await deleteRoleApi(id)
    ElMessage.success('删除成功')
    loadData()
  })
}

const permissionTypeLabel = (type: string) => {
  if (type === 'menu') return '菜单'
  if (type === 'button') return '按钮'
  return '接口'
}

const permissionTypeTag = (type: string) => {
  if (type === 'menu') return 'success'
  if (type === 'button') return 'warning'
  return 'info'
}

onMounted(loadData)
</script>

<template>
  <ContentWrap title="角色管理">
    <div class="mb-16px">
      <el-button v-permission="'role:create'" type="primary" class="mr-12px" @click="openCreate">
        新建角色
      </el-button>
      <el-input
        v-model="queryParams.keyword"
        placeholder="搜索角色"
        clearable
        style="width: 200px"
        class="mr-12px"
      />
      <el-button type="primary" @click="loadData">搜索</el-button>
    </div>

    <el-table :data="list" v-loading="loading" border>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="name" label="角色名称" />
      <el-table-column prop="slug" label="标识" />
      <el-table-column prop="data_scope" label="数据权限" width="120">
        <template #default="scope">
          <el-tag v-if="scope?.row?.data_scope === 'all'" type="success">全部</el-tag>
          <el-tag v-else-if="scope?.row?.data_scope === 'dept'" type="warning">本部门</el-tag>
          <el-tag v-else type="info">仅本人</el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="description" label="描述" />
      <el-table-column label="操作" width="220">
        <template #default="scope">
          <el-button
            v-if="scope?.row"
            v-permission="'role:assign'"
            type="success"
            size="small"
            link
            :disabled="scope.row.slug === 'super_admin'"
            @click="openAssign(scope.row)"
          >
            授权
          </el-button>
          <el-button
            v-if="scope?.row"
            v-permission="'role:update'"
            type="primary"
            size="small"
            link
            @click="openEdit(scope.row)"
          >
            编辑
          </el-button>
          <el-button
            v-if="scope?.row"
            v-permission="'role:delete'"
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
        <el-form-item label="名称" required>
          <el-input v-model="form.name" placeholder="角色名称" />
        </el-form-item>
        <el-form-item v-if="!editingId" label="标识" required>
          <el-input v-model="form.slug" placeholder="唯一标识，如 editor" />
        </el-form-item>
        <el-form-item v-else label="标识">
          <el-input v-model="form.slug" disabled />
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="角色描述" />
        </el-form-item>
        <el-form-item label="数据权限">
          <el-select v-model="form.data_scope" style="width: 100%">
            <el-option label="全部数据" value="all" />
            <el-option label="本部门" value="dept" />
            <el-option label="仅本人" value="self" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog
      v-model="assignDialogVisible"
      :title="`分配权限 - ${assigningRole?.name || ''}`"
      width="560px"
      @closed="resetAssign"
    >
      <div v-loading="assignLoading" class="permission-tree-wrap">
        <el-tree
          ref="permissionTreeRef"
          :data="permissionTree"
          show-checkbox
          node-key="id"
          default-expand-all
          :props="{ label: 'name', children: 'children' }"
        >
          <template #default="{ data }">
            <span class="permission-tree-node">
              <span>{{ data.name }}</span>
              <el-tag size="small" :type="permissionTypeTag(data.type)" class="ml-8px">
                {{ permissionTypeLabel(data.type) }}
              </el-tag>
            </span>
          </template>
        </el-tree>
      </div>
      <template #footer>
        <el-button @click="assignDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="assignSubmitting" @click="handleAssignSubmit">
          保存
        </el-button>
      </template>
    </el-dialog>
  </ContentWrap>
</template>

<style scoped>
.permission-tree-wrap {
  max-height: 480px;
  overflow: auto;
  border: 1px solid var(--el-border-color-light);
  border-radius: 4px;
  padding: 12px;
}

.permission-tree-node {
  display: inline-flex;
  align-items: center;
}
</style>
