import request from '@/axios'

export const getDictListApi = (params: any) => request.get({ url: '/admin/dicts', params })
export const createDictApi = (data: any) => request.post({ url: '/admin/dicts', data })
export const updateDictApi = (id: number, data: any) => request.put({ url: `/admin/dicts/${id}`, data })
export const deleteDictApi = (id: number) => request.delete({ url: `/admin/dicts/${id}` })
export const getDictItemsApi = (id: number) => request.get({ url: `/admin/dicts/${id}/items` })
export const saveDictItemsApi = (id: number, items: any[]) =>
  request.put({ url: `/admin/dicts/${id}/items`, data: { items } })
export const getDictByCodeApi = (code: string) => request.get({ url: `/admin/dicts/code/${code}` })
