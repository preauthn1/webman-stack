import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getConfig } from '@/api/index'
import { getTranslations } from '@/api/index.js'
import i18n from '@/i18n'

export const useConfigStore = defineStore('config', () => {
  //系统配置
  const config = ref(null)
  const setConfigFn = async (data) => {
    config.value = data
  }
  const updateLocaleList = (data) => {
    Object.keys(data).forEach((langCode) => {
      i18n.global.setLocaleMessage(langCode, data[langCode])
    })
  }

  const getTranslationsList = async () => {
    try {
      const response = await getTranslations()
      if (response.code === 0 && response.data) {
        localStorage.setItem('translations', JSON.stringify(response.data))
        updateLocaleList(response.data)
      }
    } catch (error) {
      console.error('获取翻译列表失败:', error)
    }
  }
  const getConfigFn = async () => {
    const res = await getConfig()
    if (res.code === 0) {
      setConfigFn(res.data)
      updateFavicon(res.data?.web.logo)
      setTitle(res.data?.web.title)
      const langTime = localStorage.getItem('langUpdateTime')
      if (res.data.lang_cache_time && langTime != res.data.lang_cache_time) {
        localStorage.setItem('langUpdateTime', res.data.lang_cache_time || '')
        getTranslationsList()
      } else {
        const savedLanguage = localStorage.getItem('translations')
        if (savedLanguage) {
          updateLocaleList(JSON.parse(savedLanguage))
        } else {
          getTranslationsList()
        }
      }
    }
  }
  return {
    config,
    setConfigFn,
    getConfigFn,
  }
})
const updateFavicon = (iconUrl) => {
  let link = document.querySelector("link[rel~='icon']")
  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.head.appendChild(link)
  }
  link.href = iconUrl
}
//设置标题
const setTitle = (title) => {
  document.title = title
}
