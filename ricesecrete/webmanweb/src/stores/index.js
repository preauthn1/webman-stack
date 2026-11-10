import { createPinia } from 'pinia'

import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
// 实例化 Pinia
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate);
// 导出
export default pinia