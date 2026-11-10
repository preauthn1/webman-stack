<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, ElTable, ElTableColumn, ElInput, ElButton } from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import { S3Upload } from '@/components/S3Upload'
import { getSettingListApi, updateSettingApi } from '@/api/setting'
import S3ConfigForm from './components/S3ConfigForm.vue'

const loading = ref(false)
const list = ref<any[]>([])

const defaultS3Config = () => ({
  credentials_key: '',
  credentials_secret: '',
  region: 'ap-east-1',
  bucket: '',
  url: '',
  proxy: '',
  presign_expires: 900
})

const normalizeEditValue = (item: any) => {
  if (item.key === 's3_config') {
    return {
      ...defaultS3Config(),
      ...(typeof item.value === 'object' && item.value ? item.value : {}),
      proxy: item.value?.proxy ?? ''
    }
  }

  return typeof item.value === 'string' ? item.value : JSON.stringify(item.value)
}

const loadData = async () => {
  loading.value = true
  try {
    const res = await getSettingListApi()
    list.value = (res.data || []).map((item: any) => ({
      ...item,
      editValue: normalizeEditValue(item)
    }))
  } finally {
    loading.value = false
  }
}

const handleSave = async (row: any) => {
  let value: any = row.editValue

  if (row.key === 's3_config') {
    value = {
      ...row.editValue,
      proxy: row.editValue.proxy || null
    }
  } else {
    try {
      value = JSON.parse(row.editValue)
    } catch {
      // keep as string
    }
  }

  await updateSettingApi(row.key, value)
  ElMessage.success('保存成功')
  loadData()
}

const handleLogoUploadSuccess = async (row: any) => {
  await handleSave(row)
}

onMounted(loadData)
</script>

<template>
  <ContentWrap title="系统配置">
    <el-table :data="list" v-loading="loading" border>
      <el-table-column prop="key" label="配置键" width="200" />
      <el-table-column prop="description" label="描述" width="200" />
      <el-table-column label="值">
        <template #default="scope">
          <S3ConfigForm v-if="scope?.row?.key === 's3_config'" v-model="scope.row.editValue" />
          <S3Upload
            v-else-if="scope?.row?.key === 'site_logo'"
            v-model="scope.row.editValue"
            accept="image/jpeg,image/png,image/gif,image/webp"
            @success="handleLogoUploadSuccess(scope.row)"
          />
          <el-input v-else-if="scope?.row" v-model="scope.row.editValue" />
        </template>
      </el-table-column>
      <el-table-column label="操作" width="120">
        <template #default="scope">
          <el-button
            v-if="scope?.row"
            v-permission="'setting:update'"
            type="primary"
            size="small"
            link
            @click="handleSave(scope.row)"
          >
            保存
          </el-button>
        </template>
      </el-table-column>
    </el-table>
  </ContentWrap>
</template>
