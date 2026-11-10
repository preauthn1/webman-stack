export interface S3UploadProps {
  modelValue?: string
  type?: 'image' | 'document'
  accept?: string
  disabled?: boolean
  showFileList?: boolean
  limit?: number
}

export interface S3UploadEmits {
  (e: 'update:modelValue', value: string): void
  (e: 'success', value: { url: string; filename: string; size: number }): void
  (e: 'error', error: Error): void
}
