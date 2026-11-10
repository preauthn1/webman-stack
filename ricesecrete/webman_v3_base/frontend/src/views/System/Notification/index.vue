<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ElMessage,
  ElTable,
  ElTableColumn,
  ElButton,
  ElPagination,
  ElTag,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput
} from 'element-plus'
import { ContentWrap } from '@/components/ContentWrap'
import {
  getNotificationListApi,
  markNotificationReadApi,
  markAllNotificationReadApi,
  createNotificationApi
} from '@/api/notification'

const loading = ref(false)
const list = ref<any[]>([])
const total = ref(0)
const queryParams = ref({ page: 1, limit: 10, is_read: '' })
const createVisible = ref(false)
const createForm = ref({ title: '', content: '', type: 'notice', admin_id: 0 })

const loadData = async () => {
  loading.value = true
  try {
    const res = await getNotificationListApi(queryParams.value)
    list.value = res.data?.items || []
    total.value = res.data?.total || 0
  } finally {
    loading.value = false
  }
}

const markRead = async (row: any) => {
  await markNotificationReadApi(row.id)
  ElMessage.success('已标记为已读')
  loadData()
}

const markAllRead = async () => {
  await markAllNotificationReadApi()
  ElMessage.success('全部已读')
  loadData()
}

const handleCreate = async () => {
  if (!createForm.value.title || !createForm.value.content) {
    ElMessage.warning('标题和内容不能为空')
    return
  }
  await createNotificationApi(createForm.value)
  ElMessage.success('发布成功')
  createVisible.value = false
  createForm.value = { title: '', content: '', type: 'notice', admin_id: 0 }
  loadData()
}

onMounted(loadData)
</script>

<template>
  <ContentWrap>
    <div class="mb-4 flex gap-2">
      <ElButton type="primary" @click="loadData">刷新</ElButton>
      <ElButton type="success" @click="createVisible = true">发布公告</ElButton>
      <ElButton @click="markAllRead">全部已读</ElButton>
    </div>

    <ElTable v-loading="loading" :data="list" border>
      <ElTableColumn prop="title" label="标题" />
      <ElTableColumn prop="type" label="类型" width="120">
        <template #default="{ row }">
          <ElTag>{{ row.type }}</ElTag>
        </template>
      </ElTableColumn>
      <ElTableColumn label="状态" width="100">
        <template #default="{ row }">
          <ElTag :type="row.is_read ? 'info' : 'warning'">
            {{ row.is_read ? '已读' : '未读' }}
          </ElTag>
        </template>
      </ElTableColumn>
      <ElTableColumn prop="created_at" label="时间" width="180" />
      <ElTableColumn label="操作" width="120">
        <template #default="{ row }">
          <ElButton v-if="!row.is_read" link type="primary" @click="markRead(row)">
            标记已读
          </ElButton>
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

    <ElDialog v-model="createVisible" title="发布公告" width="560px">
      <ElForm label-width="80px">
        <ElFormItem label="标题">
          <ElInput v-model="createForm.title" />
        </ElFormItem>
        <ElFormItem label="内容">
          <ElInput v-model="createForm.content" type="textarea" :rows="5" />
        </ElFormItem>
      </ElForm>
      <template #footer>
        <ElButton @click="createVisible = false">取消</ElButton>
        <ElButton type="primary" @click="handleCreate">发布</ElButton>
      </template>
    </ElDialog>
  </ContentWrap>
</template>
