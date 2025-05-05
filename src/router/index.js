import { createRouter, createWebHashHistory } from 'vue-router'
import General from '../views/General.vue'

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
    component: () => import('../views/Settings.vue'),
    meta: { keepAlive: true }
  },
  {
    path: '/tools',
    name: 'Tools',
    component: () => import('../views/Tools.vue'),
    meta: { keepAlive: true }
  },
  {
    path: '/license',
    name: 'License',
    component: () => import('../views/License.vue'),
    meta: { keepAlive: true }
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
