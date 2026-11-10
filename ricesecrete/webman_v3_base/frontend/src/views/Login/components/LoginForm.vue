<script setup lang="tsx">
import { reactive, ref, watch, onMounted, unref } from 'vue'
import { Form, FormSchema } from '@/components/Form'
import { useI18n } from '@/hooks/web/useI18n'
import { ElCheckbox, ElLink, ElAlert } from 'element-plus'
import { useForm } from '@/hooks/web/useForm'
import { loginApi, getMenusApi, getMeApi, getCaptchaApi, getLoginStatusApi } from '@/api/login'
import { getDefaultRoutePath } from '@/utils/menuTransform'
import { normalizeRoleSlugs } from '@/utils/role'
import { useAppStore } from '@/store/modules/app'
import { usePermissionStore } from '@/store/modules/permission'
import { useRouter } from 'vue-router'
import type { RouteLocationNormalizedLoaded, RouteRecordRaw } from 'vue-router'
import { UserType } from '@/api/login/types'
import { useValidator } from '@/hooks/web/useValidator'
import { useUserStore } from '@/store/modules/user'
import { BaseButton } from '@/components/Button'

const { required } = useValidator()

const appStore = useAppStore()

const userStore = useUserStore()

const permissionStore = usePermissionStore()

const { currentRoute, addRoute, push } = useRouter()

const { t } = useI18n()

const rules = {
  username: [required()],
  password: [required()],
  captcha_answer: [
    {
      validator: (_rule: any, value: string, callback: (error?: Error) => void) => {
        if (captchaRequired.value && !value) {
          callback(new Error('请输入验证码'))
          return
        }
        callback()
      },
      trigger: 'blur'
    }
  ]
}

const schema = reactive<FormSchema[]>([
  {
    field: 'title',
    colProps: {
      span: 24
    },
    formItemProps: {
      slots: {
        default: () => {
          return <h2 class="text-2xl font-bold text-center w-[100%]">{t('login.login')}</h2>
        }
      }
    }
  },
  {
    field: 'username',
    label: t('login.username'),
    // value: 'admin',
    component: 'Input',
    colProps: {
      span: 24
    },
    componentProps: {
      placeholder: 'admin'
    }
  },
  {
    field: 'password',
    label: t('login.password'),
    // value: 'admin',
    component: 'InputPassword',
    colProps: {
      span: 24
    },
    componentProps: {
      style: {
        width: '100%'
      },
      placeholder: 'admin',
      // 按下enter键触发登录
      onKeydown: (_e: any) => {
        if (_e.key === 'Enter') {
          _e.stopPropagation() // 阻止事件冒泡
          signIn()
        }
      }
    }
  },
  {
    field: 'captcha_answer',
    label: '验证码',
    component: 'Input',
    colProps: {
      span: 24
    },
    hidden: true,
    componentProps: {
      placeholder: '请输入计算结果',
      onKeydown: (_e: any) => {
        if (_e.key === 'Enter') {
          _e.stopPropagation()
          signIn()
        }
      }
    }
  },
  {
    field: 'captcha_tip',
    colProps: {
      span: 24
    },
    hidden: true,
    formItemProps: {
      slots: {
        default: () => {
          if (!captchaQuestion.value) return null
          return <div class="text-sm text-gray-500">验证码：{captchaQuestion.value}</div>
        }
      }
    }
  },
  {
    field: 'error',
    colProps: {
      span: 24
    },
    formItemProps: {
      slots: {
        default: () => {
          if (!unref(errorMessage)) return null
          return (
            <ElAlert
              title={unref(errorMessage)}
              type="error"
              show-icon
              closable
              onClose={() => {
                errorMessage.value = ''
              }}
            />
          )
        }
      }
    }
  },
  {
    field: 'tool',
    colProps: {
      span: 24
    },
    formItemProps: {
      slots: {
        default: () => {
          return (
            <>
              <div class="flex justify-between items-center w-[100%]">
                <ElCheckbox v-model={remember.value} label={t('login.remember')} size="small" />
                <ElLink type="primary" underline={false}>
                  {t('login.forgetPassword')}
                </ElLink>
              </div>
            </>
          )
        }
      }
    }
  },
  {
    field: 'login',
    colProps: {
      span: 24
    },
    formItemProps: {
      slots: {
        default: () => {
          return (
            <>
              <div class="w-[100%]">
                <BaseButton
                  loading={loading.value}
                  type="primary"
                  class="w-[100%]"
                  onClick={signIn}
                >
                  {t('login.login')}
                </BaseButton>
              </div>
            </>
          )
        }
      }
    }
  }
])

const remember = ref(userStore.getRememberMe)

const errorMessage = ref('')
const captchaRequired = ref(false)
const captchaKey = ref('')
const captchaQuestion = ref('')

const updateCaptchaSchema = () => {
  const captchaField = schema.find((item) => item.field === 'captcha_answer')
  const tipField = schema.find((item) => item.field === 'captcha_tip')
  if (captchaField) captchaField.hidden = !captchaRequired.value
  if (tipField) tipField.hidden = !captchaRequired.value
}

const loadCaptcha = async () => {
  const res = await getCaptchaApi()
  if (res.data?.enabled) {
    captchaKey.value = res.data.key || ''
    captchaQuestion.value = res.data.question || ''
  }
}

const refreshLoginStatus = async (username?: string) => {
  if (!username) {
    captchaRequired.value = false
    updateCaptchaSchema()
    return
  }
  const res = await getLoginStatusApi(username)
  captchaRequired.value = !!res.data?.captcha_required
  updateCaptchaSchema()
  if (captchaRequired.value) {
    await loadCaptcha()
  }
}

const initLoginInfo = () => {
  const savedUsername = userStore.getLoginInfo
  if (savedUsername && unref(remember)) {
    setValues({ username: savedUsername })
  }
}
onMounted(() => {
  initLoginInfo()
  loadCaptcha()
})

const { formRegister, formMethods } = useForm()
const { getFormData, getElFormExpose, setValues } = formMethods

const loading = ref(false)

const redirect = ref<string>('')

watch(
  () => currentRoute.value,
  (route: RouteLocationNormalizedLoaded) => {
    redirect.value = route?.query?.redirect as string
  },
  {
    immediate: true
  }
)

watch(
  () => remember.value,
  (newVal) => {
    userStore.setRememberMe(newVal)
    if (!newVal) {
      userStore.setLoginInfo(undefined)
    }
  }
)

// 登录
const signIn = async () => {
  const formRef = await getElFormExpose()
  await formRef?.validate(async (isValid) => {
    if (isValid) {
      loading.value = true
      errorMessage.value = ''
      const formData = await getFormData<UserType>()

      try {
        const res = await loginApi({
          username: formData.username,
          password: formData.password,
          captcha_key: captchaRequired.value ? captchaKey.value : undefined,
          captcha_answer: captchaRequired.value ? formData.captcha_answer : undefined
        })

        if (res && res.data) {
          const { token, refresh_token, user } = res.data
          userStore.setToken(token)
          userStore.setRefreshToken(refresh_token)
          const roles = user.roles || []
          userStore.setUserInfo({
            username: user.username,
            role: roles.includes('super_admin') ? 'super_admin' : roles[0] || 'admin',
            roles,
            roleId: String(user.id),
            nickname: user.nickname,
            avatar: user.avatar
          })

          if (unref(remember)) {
            userStore.setLoginInfo(formData.username)
          } else {
            userStore.setLoginInfo(undefined)
          }
          userStore.setRememberMe(unref(remember))

          if (appStore.getDynamicRouter) {
            await getRole()
          } else {
            await permissionStore.generateRoutes('static').catch(() => {})
            permissionStore.getAddRouters.forEach((route) => {
              addRoute(route as RouteRecordRaw)
            })
            permissionStore.setIsAddRouters(true)
            push({ path: redirect.value || permissionStore.addRouters[0].path })
          }
        }
      } catch (error: any) {
        errorMessage.value = error?.message || '登录失败，请检查用户名和密码'
        await refreshLoginStatus(formData.username)
      } finally {
        loading.value = false
      }
    }
  })
}

// 获取角色信息
const getRole = async () => {
  try {
    const meRes = await getMeApi()
    if (meRes?.data?.permissions) {
      userStore.setPermissions(meRes.data.permissions)
    }
    if (meRes?.data?.roles?.length) {
      const roles = normalizeRoleSlugs(meRes.data.roles)
      userStore.setUserInfo({
        ...userStore.getUserInfo,
        username: meRes.data.username,
        nickname: meRes.data.nickname,
        avatar: meRes.data.avatar,
        roles,
        role: roles.includes('super_admin') ? 'super_admin' : roles[0]
      })
    }

    const res = await getMenusApi()
    if (res) {
      const routers = res.data || []
      userStore.setRoleRouters(routers)
      await permissionStore.generateRoutes('server', routers).catch(() => {})

      permissionStore.getAddRouters.forEach((route) => {
        addRoute(route as RouteRecordRaw)
      })
      permissionStore.setIsAddRouters(true)
      const defaultPath = getDefaultRoutePath(routers)
      push({ path: redirect.value || defaultPath })
    }
  } catch {
    errorMessage.value = '获取菜单失败，请重试'
  }
}
</script>

<template>
  <Form
    :schema="schema"
    :rules="rules"
    label-position="top"
    hide-required-asterisk
    size="large"
    class="dark:(border-1 border-[var(--el-border-color)] border-solid)"
    @register="formRegister"
  />
</template>
