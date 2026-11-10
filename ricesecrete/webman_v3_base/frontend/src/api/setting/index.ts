import request from '@/axios'

export const getSettingListApi = () => {
  return request.get({ url: '/admin/settings' })
}

export const updateSettingApi = (key: string, value: any) => {
  return request.put({ url: `/admin/settings/${key}`, data: { value } })
}

export const batchUpdateSettingsApi = (settings: { key: string; value: any }[]) => {
  return request.put({ url: '/admin/settings', data: { settings } })
}
