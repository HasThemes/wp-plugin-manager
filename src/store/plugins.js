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
        const response = await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            action: 'htpm_get_plugins',
            nonce: window.htpmData.nonce
          })
        })
        const data = await response.json()
        if (data.success) {
          this.plugins = data.data
        } else {
          throw new Error(data.message)
        }
      } catch (error) {
        this.error = error.message
      } finally {
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
