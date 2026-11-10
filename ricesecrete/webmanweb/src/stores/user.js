import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getUserInfo } from '@/api/index'
import i18n from '@/i18n'
import { Locale } from 'vant'
import zhCN from 'vant/es/locale/lang/zh-CN'
import enUS from 'vant/es/locale/lang/en-US'
export const useUserStore = defineStore('user', () => {
  //用户信息
  const userInfo = ref(null)
  const token = ref(null)
  const is_login = ref(false)
  const setUserInfo = (data) => {
    userInfo.value = data
  }

  const setToken = (data) => {
    token.value = data
    is_login.value = true
  }

  const getUserInfoFn = async () => {
    const res = await getUserInfo()
    if (res.code === 0) {
      setUserInfo(res.data)
      // 根据用户信息中的lang字段切换语言
      if (res.data.lang) {
        i18n.global.locale.value = res.data.lang
        localStorage.setItem('language', res.data.lang)

        // 设置Vant组件库的多语言
        if (res.data.lang === 'zh' || res.data.lang === 'zh-CN') {
          Locale.use('zh-CN', zhCN)
        } else if (res.data.lang === 'en' || res.data.lang === 'en-US') {
          Locale.use('en-US', enUS)
        }
      }
    }
  }

  const clearUserInfo = () => {
    userInfo.value = null
    token.value = null
    is_login.value = false
  }

  return {
    userInfo,
    token,
    is_login,
    setUserInfo,
    setToken,
    getUserInfoFn,
    clearUserInfo,
  }
},
  {
    persist: true
  }
);
