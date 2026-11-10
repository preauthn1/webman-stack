<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  ElUpload,
  ElMessage,
  ElDialog,
  type UploadRequestOptions,
  type UploadUserFile
} from 'element-plus'
import { Icon } from '@/components/Icon'
import { uploadToS3 } from '@/utils/s3Upload'
import type { S3UploadEmits, S3UploadProps } from './types'

const props = withDefaults(defineProps<S3UploadProps>(), {
  type: 'image',
  accept: 'image/jpeg,image/png,image/gif,image/webp',
  disabled: false,
  showFileList: true,
  limit: 1
})

const emit = defineEmits<S3UploadEmits>()

const uploading = ref(false)
const fileList = ref<UploadUserFile[]>([])
const previewVisible = ref(false)
const previewUrl = ref('')

const isImage = computed(() => props.type === 'image')

const currentUrl = computed({
  get: () => props.modelValue ?? '',
  set: (value: string) => emit('update:modelValue', value)
})

const syncFileList = () => {
  const url = currentUrl.value
  fileList.value = url
    ? [
        {
          name: url.split('/').pop() || 'image',
          url,
          status: 'success',
          uid: 1
        }
      ]
    : []
}

const isLimitReached = computed(() => fileList.value.length >= props.limit)

watch(
  () => props.modelValue,
  () => syncFileList(),
  { immediate: true }
)

const handleUpload = async (options: UploadRequestOptions) => {
  uploading.value = true
  try {
    const result = await uploadToS3(options.file as File, { type: props.type })
    currentUrl.value = result.url
    syncFileList()
    emit('success', result)
    ElMessage.success('上传成功')
    options.onSuccess?.(result)
  } catch (error) {
    const err = error instanceof Error ? error : new Error('上传失败')
    emit('error', err)
    ElMessage.error(err.message)
    options.onError?.(err as any)
  } finally {
    uploading.value = false
  }
}

const handleRemove = () => {
  currentUrl.value = ''
  fileList.value = []
}

const handlePreview = (file: UploadUserFile) => {
  previewUrl.value = file.url || currentUrl.value
  previewVisible.value = true
}
</script>

<template>
  <div class="s3-upload">
    <ElUpload
      v-model:file-list="fileList"
      class="s3-upload__uploader"
      :class="{
        's3-upload__uploader--image': isImage,
        's3-upload__uploader--limit': isLimitReached
      }"
      :accept="accept"
      :disabled="disabled || uploading"
      :show-file-list="showFileList"
      :list-type="isImage ? 'picture-card' : 'text'"
      :limit="limit"
      :http-request="handleUpload"
      :on-remove="handleRemove"
      :on-preview="handlePreview"
    >
      <template v-if="isImage && !isLimitReached">
        <Icon icon="vi-ep:plus" :size="24" />
      </template>
      <template v-else>
        <el-button type="primary" :loading="uploading">点击上传</el-button>
      </template>
    </ElUpload>

    <ElDialog v-model="previewVisible" title="图片预览" width="520px" append-to-body>
      <img class="s3-upload__preview-image" :src="previewUrl" alt="preview" />
    </ElDialog>
  </div>
</template>

<style scoped>
.s3-upload__uploader--image :deep(.el-upload-list--picture-card) {
  margin: 0;
}

.s3-upload__uploader--image.s3-upload__uploader--limit :deep(.el-upload--picture-card) {
  display: none;
}

.s3-upload__preview-image {
  display: block;
  width: 100%;
  max-height: 420px;
  object-fit: contain;
}
</style>
