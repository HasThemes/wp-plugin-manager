import { createRouter, createWebHashHistory } from 'vue-router'
import General from '../views/General.vue'

const routes = [
  {
    path: '/',
    name: 'General',
    component: General
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('../views/Settings.vue')
  },
  {
    path: '/tools',
    name: 'Tools',
    component: () => import('../views/Tools.vue')
  },
  {
    path: '/license',
    name: 'License',
    component: () => import('../views/License.vue')
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
