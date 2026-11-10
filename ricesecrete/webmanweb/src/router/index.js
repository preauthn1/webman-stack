import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/home'
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/Login.vue'),
      meta: {
        showTabbar: false,
        requiresAuth: false // 登录页面不需要验证
      }
    },
    {
      path: '/home',
      name: 'home',
      component: () => import('../views/Home.vue'),
      meta: {
        showTabbar: true, //控制是否显示底部导航
        requiresAuth: false // 需要登录验证
      }
    },
  ],
})

const whiteList = ["/login", "/register"]
// 路由守卫 - 检查登录状态
router.beforeEach((to, from, next) => {
  const store = useUserStore();
  if (!whiteList.includes(to.path)) {
    if (store.is_login) {
      next()
    } else {
      router.push("/login")
    }
  } else {
    next();
  }

})

export default router
