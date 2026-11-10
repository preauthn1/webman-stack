export interface Article {
  id: number
  title: string
  summary: string
  content: string
  category_id: number | null
  status: 0 | 1
  author_id: number
  view_count: number
  created_at: string
  updated_at: string
  author?: { id: number; nickname: string }
  category?: { id: number; name: string }
}

export interface ArticleListParams {
  page?: number
  limit?: number
  keyword?: string
  status?: number | ''
  category_id?: number
}

export interface PaginatedResult<T> {
  total: number
  items: T[]
}
