import request from '@/axios'
import type { Article, ArticleListParams } from '@/types/article'

export const getArticleListApi = (params: ArticleListParams) => {
  return request.get<{ total: number; items: Article[] }>({ url: '/admin/articles', params })
}

export const getArticleDetailApi = (id: number) => {
  return request.get<Article>({ url: `/admin/articles/${id}` })
}

export const createArticleApi = (data: Partial<Article>) => {
  return request.post({ url: '/admin/articles', data })
}

export const updateArticleApi = (id: number, data: Partial<Article>) => {
  return request.put({ url: `/admin/articles/${id}`, data })
}

export const deleteArticleApi = (id: number) => {
  return request.delete({ url: `/admin/articles/${id}` })
}
