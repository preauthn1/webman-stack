<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElTag,
  ElButton,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElSelect,
  ElOption,
  ElInputNumber,
  ElSwitch
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import {
  getPermissionListApi,
  createPermissionApi,
  updatePermissionApi,
  deletePermissionApi
} from '@/api/permission'

interface PermissionNode {
  id: number
  name: string
  slug: string
  type: string
  path?: string
  method?: string
  icon?: string
  component?: string
  sort?: number
  hidden?: number
  parent_id?: number
  children?: PermissionNode[]
}

const loading = ref(false)
const treeData = ref<PermissionNode[]>([])
const dialogVisible = ref(false)
const dialogTitle = ref('新建权限')
const editingId = ref<number | null>(null)

const defaultForm = () => ({
  name: '',
  slug: '',
  type: 'api' as 'menu' | 'api',
  parent_id: 0,
  path: '',
  method: 'GET',
  icon: '',
  component: '',
  sort: 0,
  hidden: 0
})

const form = ref(defaultForm())

const flattenTree = (nodes: PermissionNode[], result: PermissionNode[] = []): PermissionNode[] => {
  for (const node of nodes) {
    result.push(node)
    if (node.children?.length) {
      flattenTree(node.children, result)
    }
  }
  return result
}

const parentOptions = computed(() => {
  return flattenTree(treeData.value).filter((item) => item.type === 'menu')
})

const resetForm = () => {
  editingId.value = null
  form.value = defaultForm()
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getPermissionListApi()
    treeData.value = res.data ?? []
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  resetForm()
  dialogTitle.value = '新建权限'
  dialogVisible.value = true
}

const openEdit = (row: PermissionNode) => {
  editingId.value = row.id
  dialogTitle.value = '编辑权限'
  form.value = {
    name: row.name,
    slug: row.slug,
    type: row.type as 'menu' | 'api',
    parent_id: row.parent_id ?? 0,
    path: row.path || '',
    method: row.method || 'GET',
    icon: row.icon || '',
    component: row.component || '',
    sort: row.sort ?? 0,
    hidden: row.hidden ?? 0
  }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!form.value.name.trim()) {
    ElMessage.warning('名称不能为空')
    return
  }
  if (!editingId.value && !form.value.slug.trim()) {
    ElMessage.warning('标识不能为空')
    return
  }

  try {
    if (editingId.value) {
      await updatePermissionApi(editingId.value, {
        name: form.value.name,
        path: form.value.path,
        method: form.value.type === 'api' ? form.value.method : '',
        icon: form.value.icon,
        component: form.value.component,
        sort: form.value.sort,
        hidden: form.value.hidden
      })
      ElMessage.success('更新成功')
    } else {
      await createPermissionApi({
        ...form.value,
        method: form.value.type === 'api' ? form.value.method : ''
      })
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    resetForm()
    loadData()
  } catch {
    ElMessage.error(editingId.value ? '更新失败' : '创建失败')
  }
}

const handleDelete = (row: PermissionNode) => {
  ElMessageBox.confirm(`确认删除权限「${row.name}」吗？`, '提示', { type: 'warning' }).then(
    async () => {
      await deletePermissionApi(row.id)
      ElMessage.success('删除成功')
      loadData()
    }
  )
}

onMounted(loadData)
</script>

<template>
  <ContentWrap title="权限管理">
    <ElButton v-permission="'permission:create'" type="primary" class="mb-16px" @click="openCreate">
      新建权限
    </ElButton>

    <ElTable
      :data="treeData"
      v-loading="loading"
      border
      row-key="id"
      default-expand-all
      :tree-props="{ children: 'children' }"
    >
      <ElTableColumn prop="name" label="名称" min-width="160" />
      <ElTableColumn prop="slug" label="标识" min-width="160" />
      <ElTableColumn prop="type" label="类型" width="100">
        <template #default="scope">
          <ElTag v-if="scope?.row" :type="scope.row.type === 'menu' ? 'success' : 'info'">
            {{ scope.row.type === 'menu' ? '菜单' : '接口' }}
          </ElTag>
        </template>
      </ElTableColumn>
      <ElTableColumn prop="path" label="路径" min-width="200" />
      <ElTableColumn prop="method" label="方法" width="100" />
      <ElTableColumn prop="sort" label="排序" width="80" />
      <ElTableColumn label="操作" width="160" fixed="right">
        <template #default="scope">
          <ElButton
            v-if="scope?.row"
            v-permission="'permission:update'"
            type="primary"
            size="small"
            link
            @click="openEdit(scope.row)"
          >
            编辑
          </ElButton>
          <ElButton
            v-if="scope?.row"
            v-permission="'permission:delete'"
            type="danger"
            size="small"
            link
            @click="handleDelete(scope.row)"
          >
            删除
          </ElButton>
        </template>
      </ElTableColumn>
    </ElTable>

    <ElDialog v-model="dialogVisible" :title="dialogTitle" width="520px" @closed="resetForm">
      <ElForm :model="form" label-width="90px">
        <ElFormItem label="名称" required>
          <ElInput v-model="form.name" placeholder="权限名称" />
        </ElFormItem>
        <ElFormItem v-if="!editingId" label="标识" required>
          <ElInput v-model="form.slug" placeholder="唯一标识，如 article:list" />
        </ElFormItem>
        <ElFormItem v-else label="标识">
          <ElInput v-model="form.slug" disabled />
        </ElFormItem>
        <ElFormItem v-if="!editingId" label="类型" required>
          <ElSelect v-model="form.type" style="width: 100%">
            <ElOption label="菜单" value="menu" />
            <ElOption label="接口" value="api" />
          </ElSelect>
        </ElFormItem>
        <ElFormItem v-if="!editingId" label="上级">
          <ElSelect v-model="form.parent_id" style="width: 100%">
            <ElOption label="无（顶级）" :value="0" />
            <ElOption
              v-for="item in parentOptions"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </ElSelect>
        </ElFormItem>
        <ElFormItem label="路径">
          <ElInput v-model="form.path" placeholder="路由或 API 路径" />
        </ElFormItem>
        <ElFormItem v-if="form.type === 'api'" label="方法">
          <ElSelect v-model="form.method" style="width: 100%">
            <ElOption label="GET" value="GET" />
            <ElOption label="POST" value="POST" />
            <ElOption label="PUT" value="PUT" />
            <ElOption label="DELETE" value="DELETE" />
          </ElSelect>
        </ElFormItem>
        <ElFormItem v-if="form.type === 'menu'" label="图标">
          <ElInput v-model="form.icon" placeholder="菜单图标" />
        </ElFormItem>
        <ElFormItem v-if="form.type === 'menu'" label="组件">
          <ElInput v-model="form.component" placeholder="如 views/article/index" />
        </ElFormItem>
        <ElFormItem label="排序">
          <ElInputNumber v-model="form.sort" :min="0" />
        </ElFormItem>
        <ElFormItem v-if="form.type === 'menu'" label="隐藏">
          <ElSwitch v-model="form.hidden" :active-value="1" :inactive-value="0" />
        </ElFormItem>
      </ElForm>
      <template #footer>
        <ElButton @click="dialogVisible = false">取消</ElButton>
        <ElButton type="primary" @click="handleSubmit">确定</ElButton>
      </template>
    </ElDialog>
  </ContentWrap>
</template>
