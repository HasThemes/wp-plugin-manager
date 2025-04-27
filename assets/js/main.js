import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import 'element-plus/dist/index.css'
import App from './App.vue'

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  // Create Vue app
  const app = createApp(App)

  // Register Element Plus Icons
  for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
    app.component(key, component)
  }

  // Use plugins
  app.use(createPinia())
  app.use(ElementPlus, {
    size: 'default',
    zIndex: 3000
  })

  // Mount app
  const mountPoint = document.getElementById('htpm-app')
  if (mountPoint) {
    app.mount(mountPoint)
  } else {
    console.error('Could not find #htpm-app element')
  }
})
