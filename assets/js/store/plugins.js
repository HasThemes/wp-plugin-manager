import { defineStore } from 'pinia'

export const usePluginStore = defineStore('plugins', {
  state: () => ({
    plugins: [
      {
        id: 1,
        name: 'Query Monitor',
        icon: 'path/to/icon.png',
        active: true,
        settings: {
          disabled: false,
          deviceType: 'desktop_tablet',
          action: 'enable',
          pageType: 'custom',
          uriCondition: ''
        }
      },
      // More plugins...
    ]
  }),

  actions: {
    async fetchPlugins() {
      try {
        const response = await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            action: 'htpm_get_plugins'
          })
        })
        
        if (!response.ok) throw new Error('Failed to fetch plugins')
        
        const data = await response.json()
        if (data.success) {
          this.plugins = data.data
        }
      } catch (error) {
        console.error('Error fetching plugins:', error)
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
            action: 'htpm_ajax_plugin_activation',
            plugin: plugin.id,
            active: plugin.active
          })
        })

        if (!response.ok) throw new Error('Failed to toggle plugin')

        const data = await response.json()
        if (!data.success) {
          plugin.active = !plugin.active // Revert the change
          throw new Error(data.message)
        }
      } catch (error) {
        console.error('Error toggling plugin:', error)
      }
    },

    async updatePluginSettings({ plugin, settings }) {
      try {
        const response = await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            action: 'htpm_update_plugin_settings',
            plugin: plugin.id,
            settings: JSON.stringify(settings)
          })
        })

        if (!response.ok) throw new Error('Failed to update plugin settings')

        const data = await response.json()
        if (data.success) {
          const index = this.plugins.findIndex(p => p.id === plugin.id)
          if (index !== -1) {
            this.plugins[index].settings = settings
          }
        }
      } catch (error) {
        console.error('Error updating plugin settings:', error)
      }
    }
  }
})
