// utils/authCheck.js
import { useUserStore } from '@/stores/user'
import router from '@/router'
import { showToast } from 'vant'

/**
 * 检查用户登录状态的组合式函数
 * @returns {Object} 包含检查登录状态和处理点击事件的方法
 */
export function useAuthCheck() {
    const userStore = useUserStore()

    /**
     * 检查用户是否已登录
     * @returns {boolean} 登录状态
     */
    const checkLoginStatus = () => {
        return userStore.isLoggedIn && userStore.token
    }

    /**
     * 处理需要登录的点击事件
     * @param {Function} callback - 登录后执行的回调函数
     * @param {string} redirectPath - 登录后跳转的页面路径（可选）
     */
    const handleAuthenticatedClick = (callback, redirectPath = null) => {
        if (checkLoginStatus()) {
            // 已登录，执行回调函数
            if (typeof callback === 'function') {
                callback()
            }
        } else {
            // 未登录，显示提示并跳转到登录页
            showToast({
                message: '请先登录',
                position: 'top'
            })

            const currentPath = redirectPath || router.currentRoute.value.fullPath
            router.push({
                path: '/login',
                query: { redirect: currentPath }
            })
        }
    }

    /**
     * 处理需要登录的路由跳转
     * @param {string} routePath - 目标路由路径
     * @param {Object} routeParams - 路由参数（可选）
     */
    const handleAuthenticatedNavigation = (routePath, routeParams = {}) => {
        if (checkLoginStatus()) {
            // 已登录，直接跳转
            router.push({
                path: routePath,
                ...routeParams
            })
        } else {
            // 未登录，跳转到登录页并保存目标路径
            showToast({
                message: '请先登录',
                position: 'top'
            })

            router.push({
                path: '/login',
                query: {
                    redirect: routePath,
                    ...routeParams.query
                }
            })
        }
    }

    return {
        checkLoginStatus,
        handleAuthenticatedClick,
        handleAuthenticatedNavigation
    }
}