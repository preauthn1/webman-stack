import type { App, Directive, DirectiveBinding } from 'vue'
import { useUserStoreWithOut } from '@/store/modules/user'

const isSuperAdmin = (): boolean => {
  const userStore = useUserStoreWithOut()
  const userInfo = userStore.getUserInfo
  const roles = userInfo?.roles?.length ? userInfo.roles : userInfo?.role ? [userInfo.role] : []
  return roles.includes('super_admin')
}

const checkPermission = (value: string): boolean => {
  if (!value) {
    return false
  }
  if (isSuperAdmin()) {
    return true
  }
  const userStore = useUserStoreWithOut()
  return userStore.getPermissions.includes(value)
}

const applyPermission = (el: HTMLElement, binding: DirectiveBinding<string>) => {
  el.style.display = checkPermission(binding.value) ? '' : 'none'
}

const permissionDirective: Directive = {
  mounted(el: Element, binding: DirectiveBinding<string>) {
    applyPermission(el as HTMLElement, binding)
  },
  updated(el: Element, binding: DirectiveBinding<string>) {
    applyPermission(el as HTMLElement, binding)
  }
}

export const setupPermissionDirective = (app: App<Element>) => {
  app.directive('permission', permissionDirective)
}
