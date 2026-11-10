import request from '@/axios'

export const getAdminListApi = (params: Record<string, any>) => {
  return request.get({ url: '/admin/admins', params })
}

export const createAdminApi = (data: Record<string, any>) => {
  return request.post({ url: '/admin/admins', data })
}

export const updateAdminApi = (id: number, data: Record<string, any>) => {
  return request.put({ url: `/admin/admins/${id}`, data })
}

export const deleteAdminApi = (id: number) => {
  return request.delete({ url: `/admin/admins/${id}` })
}

export const updateAdminPasswordApi = (id: number, data: Record<string, string>) => {
  return request.put({ url: `/admin/admins/${id}/password`, data })
}
