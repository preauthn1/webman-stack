export interface UserLoginType {
  username: string
  password: string
  captcha_key?: string
  captcha_answer?: string
}

export interface LoginResult {
  token: string
  refresh_token: string
  expires_in: number
  user: UserInfo
}

export interface UserInfo {
  id: number
  username: string
  nickname: string
  avatar: string
  roles: Array<string | { id: number; name: string; slug: string }>
  permissions?: string[]
}

export interface UserType {
  username: string
  password?: string
  role: string
  roles?: string[]
  roleId: string
  nickname?: string
  avatar?: string
  permissions?: string[]
}
