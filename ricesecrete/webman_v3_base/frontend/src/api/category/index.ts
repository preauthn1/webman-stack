import request from '@/axios'

export const getCategoryListApi = () => {
  return request.get({ url: '/admin/categories' })
}

export const createCategoryApi = (data: Record<string, any>) => {
  return request.post({ url: '/admin/categories', data })
}

export const updateCategoryApi = (id: number, data: Record<string, any>) => {
  return request.put({ url: `/admin/categories/${id}`, data })
}

export const deleteCategoryApi = (id: number) => {
  return request.delete({ url: `/admin/categories/${id}` })
}
