import { defineStore } from 'pinia'

export const usePluginStore = defineStore('plugins', {
  state: () => ({
    plugins: [],
    loading: false,
    error: null
  }),

  getters: {
    activePlugins: (state) => state.plugins.filter(p => p.active),
    inactivePlugins: (state) => state.plugins.filter(p => !p.active),
    updateAvailable: (state) => state.plugins.filter(p => p.updateAvailable),
    totalPlugins: (state) => state.plugins.length
  },

  actions: {
    async fetchPlugins() {
      this.loading = true
      try {
        // Demo data for testing
        this.plugins = [
          {
            id: 1,
            name: 'Query Monitor',
            version: '3.7.1',
            author: 'John Blackbourn',
            description: 'The Developer Tools Panel for WordPress',
            active: true,
            file: 'query-monitor/query-monitor.php',
            hasUpdate: false
          },
          {
            id: 2,
            name: 'Elementor',
            version: '3.6.0',
            author: 'Elementor.com',
            description: 'The WordPress Website Builder',
            active: true,
            file: 'elementor/elementor.php',
            hasUpdate: true
          },
          {
            id: 3,
            name: 'HT Mega - Absolute Addons for Elementor',
            version: '2.1.0',
            author: 'HasThemes',
            description: 'Ultimate addons for Elementor page builder',
            active: true,
            file: 'ht-mega-for-elementor/htmega.php',
            hasUpdate: false
          },
          {
            id: 4,
            name: 'WooCommerce',
            version: '6.3.1',
            author: 'Automattic',
            description: 'An eCommerce toolkit for WordPress',
            active: false,
            file: 'woocommerce/woocommerce.php',
            hasUpdate: true
          },
          {
            id: 5,
            name: 'Yoast SEO',
            version: '18.0',
            author: 'Team Yoast',
            description: 'The first true all-in-one SEO solution for WordPress',
            active: false,
            file: 'wordpress-seo/wp-seo.php',
            hasUpdate: false
          }
        ]
        this.loading = false
      } catch (error) {
        this.error = error.message
        this.loading = false
      }
    },

    async togglePlugin(plugin) {
      try {
        const response = await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            action: 'htpm_toggle_plugin',
            plugin: plugin.file,
            active: !plugin.active,
            nonce: window.htpmData.nonce
          })
        })
        const data = await response.json()
        if (data.success) {
          const index = this.plugins.findIndex(p => p.file === plugin.file)
          if (index !== -1) {
            this.plugins[index] = { ...this.plugins[index], active: !plugin.active }
          }
        } else {
          throw new Error(data.message)
        }
      } catch (error) {
        this.error = error.message
      }
    },

    async updatePlugin(plugin) {
      try {
        const response = await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            action: 'htpm_update_plugin',
            plugin: plugin.file,
            nonce: window.htpmData.nonce
          })
        })
        const data = await response.json()
        if (data.success) {
          const index = this.plugins.findIndex(p => p.file === plugin.file)
          if (index !== -1) {
            this.plugins[index] = { ...this.plugins[index], version: data.version }
          }
        } else {
          throw new Error(data.message)
        }
      } catch (error) {
        this.error = error.message
      }
    }
  }
})
