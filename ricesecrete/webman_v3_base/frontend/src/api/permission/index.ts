import request from '@/axios'

export const getPermissionListApi = (params?: Record<string, any>) => {
  return request.get({ url: '/admin/permissions', params })
}

export const createPermissionApi = (data: Record<string, any>) => {
  return request.post({ url: '/admin/permissions', data })
}

export const updatePermissionApi = (id: number, data: Record<string, any>) => {
  return request.put({ url: `/admin/permissions/${id}`, data })
}

export const deletePermissionApi = (id: number) => {
  return request.delete({ url: `/admin/permissions/${id}` })
}
