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

        const recommendations_plugins = window.HTPMM?.adminSettings?.recommendations_plugins || [];
        this.tabs = recommendations_plugins;

        const response = await api.get('/htpm/v1/recommended-plugins')
        //const response = await axios.get('/wp-json/htpm/v1/recommended-plugins')
        
        console.log(response.data);
        //this.tabs = response.data.tabs
        this.installedPlugins = response.data.installed_plugins
        this.assetsUrl = response.data.assets_url
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
