import { createRouter, createWebHashHistory } from 'vue-router'
import General from '../views/General.vue'
import Settings from '../views/Settings.vue'
import Tools from '../views/Tools.vue'
import License from '../views/License.vue'

const routes = [
  {
    path: '/',
    name: 'General',
    component: General,
    meta: { keepAlive: true }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { keepAlive: true }
  },
  {
    path: '/tools',
    name: 'Tools',
    component: Tools,
    meta: { keepAlive: true }
  },
  {
    path: '/license',
    name: 'License',
    component: License,
    meta: { keepAlive: true }
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
