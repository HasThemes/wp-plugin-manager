import { defineStore } from 'pinia'
import api from '@/utils/axios'
export const useRecommendedPluginsStore = defineStore('recommendedPlugins', {
  state: () => ({
    tabs: [],
    installedPlugins: [],
    assetsUrl: '',
    loading: false,
    error: null
  }),

  actions: {
    async fetchTabs() {
      try {
        this.loading = true
        this.error = null

        // Get initial tabs structure
        const recommendations_plugins = window.HTPMM?.adminSettings?.recommendations_plugins || [];
        
        // Get installed plugins and assets URL
        const response = await api.get('/htpm/v1/recommended-plugins')
        this.installedPlugins = response.data.installed_plugins || []
        this.assetsUrl = response.data.assets_url

        // Fetch plugin data for each tab
        const tabsWithData = await Promise.all(recommendations_plugins.map(async (tab) => {
          if (!tab.plugins || !tab.plugins.length) return tab;
          
          // Get plugin info for all plugins in the tab
          const slugs = tab.plugins.map(p => p.slug);
          const pluginsResponse = await api.get('/htpm/v1/plugins-info', {
            params: { slugs: slugs.join(',') }
          });

          if (pluginsResponse.data.success && pluginsResponse.data.plugins) {
            // Merge WordPress.org data with our plugin data
            tab.plugins = tab.plugins.map(plugin => ({
              ...plugin,
              ...pluginsResponse.data.plugins[plugin.slug],
              isInstalled: this.installedPlugins.includes(plugin.slug)
            }));
          }
          
          return tab;
        }));

        this.tabs = tabsWithData
      } catch (error) {
        console.error('Error fetching recommended plugins:', error)
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async installPlugin(plugin) {
      try {
        const formData = new FormData()
        formData.append('action', 'htpm_ajax_plugin_activation')
        formData.append('location', plugin.location)
        formData.append('slug', plugin.slug)
        formData.append('nonce', window.HTPMM.nonce)

        const response = await axios.post(window.HTPMM.ajaxUrl, formData)
        console.log(response.data);
        if (response.data.success) {
          this.installedPlugins.push(plugin.slug)
        }
        return response.data
      } catch (error) {
        console.error('Error installing plugin:', error)
        throw error
      }
    }
  }
})
