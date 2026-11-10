import request from '@/axios'
import type { UserLoginType, LoginResult, UserInfo } from './types'
import type { AppCustomRouteRecordRaw } from '#/router'
import { transformMenus, getDefaultRoutePath } from '@/utils/menuTransform'

export const loginApi = (data: UserLoginType): Promise<IResponse<LoginResult>> => {
  return request.post({ url: '/admin/login', data })
}

export const getCaptchaApi = (): Promise<
  IResponse<{ enabled: boolean; key?: string; question?: string }>
> => {
  return request.get({ url: '/admin/captcha' })
}

export const getLoginStatusApi = (
  username: string
): Promise<IResponse<{ captcha_required: boolean }>> => {
  return request.get({ url: '/admin/login-status', params: { username } })
}

export const refreshTokenApi = (
  refresh_token: string
): Promise<IResponse<{ token: string; expires_in: number; refresh_token?: string }>> => {
  return request.post({ url: '/admin/refresh', data: { refresh_token } })
}

export const loginOutApi = (refresh_token?: string): Promise<IResponse> => {
  return request.post({ url: '/admin/logout', data: { refresh_token } })
}

export const getMeApi = (): Promise<IResponse<UserInfo>> => {
  return request.get({ url: '/admin/me' })
}

export const getMenusApi = (): Promise<IResponse<AppCustomRouteRecordRaw[]>> => {
  return request.get({ url: '/admin/menus' }).then((res: IResponse<any>) => {
    return {
      ...res,
      data: transformMenus(res.data || [])
    }
  })
}
