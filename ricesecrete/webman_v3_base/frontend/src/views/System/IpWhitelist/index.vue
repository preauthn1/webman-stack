<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ElMessage,
  ElMessageBox,
  ElTable,
  ElTableColumn,
  ElInput,
  ElButton,
  ElSwitch,
  ElPagination,
  ElDialog,
  ElForm,
  ElFormItem,
  ElTag
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import {
  getIpWhitelistListApi,
  createIpWhitelistApi,
  updateIpWhitelistApi,
  deleteIpWhitelistApi
} from '@/api/ipWhitelist'

const loading = ref(false)
const list = ref<any[]>([])
const total = ref(0)
const queryParams = ref({ page: 1, limit: 10, keyword: '' })

const dialogVisible = ref(false)
const dialogTitle = ref('新增白名单')
const editingId = ref<number | null>(null)
const form = ref({
  ip_rule: '',
  remark: '',
  enabled: 1
})

const normalizeList = (items: any[]) =>
  (items || []).map((item) => ({
    ...item,
    enabled: Number(item.enabled) === 1 ? 1 : 0,
    remark: item.remark ?? ''
  }))

const resetForm = () => {
  editingId.value = null
  form.value = { ip_rule: '', remark: '', enabled: 1 }
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getIpWhitelistListApi(queryParams.value)
    list.value = normalizeList(res.data?.items || [])
    total.value = res.data?.total || 0
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  resetForm()
  dialogTitle.value = '新增白名单'
  dialogVisible.value = true
}

const openEdit = (row: any) => {
  editingId.value = row.id
  dialogTitle.value = '编辑白名单'
  form.value = {
    ip_rule: row.ip_rule || '',
    remark: row.remark || '',
    enabled: Number(row.enabled) === 1 ? 1 : 0
  }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!form.value.ip_rule.trim()) {
    ElMessage.warning('IP 规则不能为空')
    return
  }

  try {
    if (editingId.value) {
      await updateIpWhitelistApi(editingId.value, form.value)
      ElMessage.success('更新成功')
    } else {
      await createIpWhitelistApi(form.value)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    resetForm()
    loadData()
  } catch {
    // error message already shown by axios interceptor
  }
}

const handleToggle = async (row: any) => {
  try {
    await updateIpWhitelistApi(row.id, { enabled: Number(row.enabled) === 1 ? 1 : 0 })
    ElMessage.success('更新成功')
  } catch {
    row.enabled = Number(row.enabled) === 1 ? 0 : 1
  }
}

const handleDelete = (row: any) => {
  ElMessageBox.confirm('确认删除该 IP 白名单吗？', '提示', { type: 'warning' }).then(async () => {
    await deleteIpWhitelistApi(row.id)
    ElMessage.success('删除成功')
    loadData()
  })
}

onMounted(loadData)
</script>

<template>
  <ContentWrap title="IP 白名单">
    <div class="mb-16px">
      <el-button
        v-permission="'ipWhitelist:create'"
        type="primary"
        class="mr-12px"
        @click="openCreate"
      >
        新增白名单
      </el-button>
      <el-input
        v-model="queryParams.keyword"
        placeholder="搜索 IP / 备注"
        clearable
        style="width: 220px"
        class="mr-12px"
      />
      <el-button type="primary" @click="loadData">搜索</el-button>
    </div>

    <el-table :data="list" v-loading="loading" border>
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="ip_rule" label="IP 规则" min-width="180" />
      <el-table-column prop="remark" label="备注" min-width="200" show-overflow-tooltip />
      <el-table-column label="状态" width="100">
        <template #default="scope">
          <el-tag v-if="scope?.row" :type="scope.row.enabled === 1 ? 'success' : 'info'">
            {{ scope.row.enabled === 1 ? '启用' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="启用" width="100">
        <template #default="scope">
          <el-switch
            v-if="scope?.row"
            v-model="scope.row.enabled"
            :active-value="1"
            :inactive-value="0"
            @change="handleToggle(scope.row)"
          />
        </template>
      </el-table-column>
      <el-table-column label="操作" width="140">
        <template #default="scope">
          <el-button
            v-if="scope?.row"
            v-permission="'ipWhitelist:update'"
            type="primary"
            size="small"
            link
            @click="openEdit(scope.row)"
          >
            编辑
          </el-button>
          <el-button
            v-if="scope?.row"
            v-permission="'ipWhitelist:delete'"
            type="danger"
            size="small"
            link
            @click="handleDelete(scope.row)"
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
        <el-form-item label="IP 规则" required>
          <el-input v-model="form.ip_rule" placeholder="例如 127.0.0.1 或 192.168.1.0/24" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="form.remark" type="textarea" :rows="2" placeholder="备注说明" />
        </el-form-item>
        <el-form-item label="启用">
          <el-switch v-model="form.enabled" :active-value="1" :inactive-value="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </ContentWrap>
</template>
