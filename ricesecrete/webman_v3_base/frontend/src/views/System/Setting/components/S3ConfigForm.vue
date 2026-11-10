<script setup lang="ts">
import { ElInput, ElInputNumber } from 'element-plus'

export interface S3ConfigValue {
  credentials_key: string
  credentials_secret: string
  region: string
  bucket: string
  url: string
  proxy: string | null
  presign_expires: number
}

const model = defineModel<S3ConfigValue>({ required: true })
</script>

<template>
  <div class="s3-config-form">
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">Access Key</span>
      <ElInput v-model="model.credentials_key" placeholder="credentials_key" />
    </div>
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">Secret Key</span>
      <ElInput
        v-model="model.credentials_secret"
        type="password"
        show-password
        placeholder="credentials_secret"
      />
    </div>
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">Region</span>
      <ElInput v-model="model.region" placeholder="ap-east-1" />
    </div>
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">Bucket</span>
      <ElInput v-model="model.bucket" placeholder="bucket name" />
    </div>
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">访问 URL</span>
      <ElInput v-model="model.url" placeholder="http://bucket.s3.region.amazonaws.com" />
    </div>
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">代理</span>
      <ElInput v-model="model.proxy" placeholder="留空表示不使用代理" />
    </div>
    <div class="s3-config-form__item">
      <span class="s3-config-form__label">签名有效期（秒）</span>
      <ElInputNumber v-model="model.presign_expires" :min="60" :max="3600" />
    </div>
  </div>
</template>

<style scoped>
.s3-config-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
}

.s3-config-form__item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.s3-config-form__label {
  width: 120px;
  flex-shrink: 0;
  color: var(--el-text-color-regular);
}
</style>
