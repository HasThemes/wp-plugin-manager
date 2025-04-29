// plugins.js (Store)
import { defineStore } from 'pinia'
import axios from 'axios'

// Create an axios instance with WordPress REST API base URL and nonce
const api = axios.create({
  baseURL: window.htpmData?.restUrl || '/wp-json',
  headers: {
    'X-WP-Nonce': window.htpmData?.nonce || '',
    'Content-Type': 'application/json'
  }
})

export const usePluginStore = defineStore('plugins', {
  state: () => ({
    plugins: [],
    loading: false,
    error: null,
    settings: {},
    postTypes: [],
    pages: [],
    posts: [],
    customPostTypeItems: {}
  }),

  getters: {
    // All plugins that are active in WordPress (regardless of our loading settings)
    wpActivePlugins: (state) => state.plugins.filter(p => p.wpActive),
    
    // Plugins that are both active in WordPress and not disabled by our plugin
    activePlugins: (state) => state.plugins.filter(p => p.wpActive && !p.isDisabled),
    
    // Plugins that are disabled by our plugin (but still active in WordPress)
    disabledPlugins: (state) => state.plugins.filter(p => p.wpActive && p.isDisabled),
    
    // Plugins that are inactive in WordPress
    inactivePlugins: (state) => state.plugins.filter(p => !p.wpActive),
    
    updateAvailable: (state) => state.plugins.filter(p => p.hasUpdate),
    totalPlugins: (state) => state.plugins.length,
    pluginById: (state) => (id) => state.plugins.find(p => p.id === id),
    customPostTypes: (state) => state.postTypes.filter(type => !['page', 'post'].includes(type.name)).map(type => type.name)
  },

  actions: {
    async fetchPlugins() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get('/htpm/v1/plugins')
        this.plugins = response.data
        
        // Fetch settings for each WordPress-active plugin
        for (const plugin of this.plugins) {
          if (plugin.wpActive) {
            await this.fetchPluginSettings(plugin.id)
          }
        }
        
        return this.plugins
      } catch (error) {
        this.error = error.message || 'Failed to fetch plugins'
        console.error('Error fetching plugins:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchPluginSettings(pluginId) {
      try {
        const response = await api.get(`/htpm/v1/plugins/${pluginId}/settings`)
        
        // Store settings in the store state
        this.settings[pluginId] = response.data
        
        // Update the plugin's isDisabled state
        const pluginIndex = this.plugins.findIndex(p => p.id === pluginId)
        if (pluginIndex !== -1) {
          const isDisabled = response.data.enable_deactivation === 'yes'
          this.plugins[pluginIndex].isDisabled = isDisabled
        }
        
        return response.data
      } catch (error) {
        console.error(`Error fetching settings for plugin ${pluginId}:`, error)
        return null
      }
    },

    async updatePluginSettings(pluginId, settings) {
      try {
        // Always get the current plugin disabled state from UI
        const pluginIndex = this.plugins.findIndex(p => p.id === pluginId)
        if (pluginIndex !== -1) {
          // Ensure settings reflect the current disabled state in the UI
          settings.enable_deactivation = this.plugins[pluginIndex].isDisabled ? 'yes' : 'no'
        }
        
        const response = await api.post(`/htpm/v1/plugins/${pluginId}/settings`, settings)
        
        if (response.data.success) {
          // Update settings in store
          this.settings[pluginId] = { ...settings }
        }
        
        return response.data
      } catch (error) {
        this.error = error.message || 'Failed to update plugin settings'
        console.error('Error updating plugin settings:', error)
        throw error
      }
    },

    async togglePlugin(plugin) {
      try {
        // Toggle the isDisabled state
        const isDisabled = !plugin.isDisabled
        
        // Update the plugin in the UI immediately
        const pluginIndex = this.plugins.findIndex(p => p.id === plugin.id)
        if (pluginIndex !== -1) {
          this.plugins[pluginIndex].isDisabled = isDisabled
        }
        
        // Update the plugin settings
        const settings = this.settings[plugin.id] || {
          enable_deactivation: isDisabled ? 'yes' : 'no',
          device_type: 'all',
          condition_type: 'disable_on_selected',
          uri_type: 'page',
          post_types: ['page', 'post'],
          posts: [],
          pages: [],
          condition_list: {
            name: ['uri_equals'],
            value: [''],
          }
        }
        
        // Update the enable_deactivation setting
        settings.enable_deactivation = isDisabled ? 'yes' : 'no'
        
        // Save the updated settings
        const response = await this.updatePluginSettings(plugin.id, settings)
        
        // If the update fails, revert the UI change
        if (!response.success) {
          const revertIndex = this.plugins.findIndex(p => p.id === plugin.id)
          if (revertIndex !== -1) {
            this.plugins[revertIndex].isDisabled = !isDisabled
          }
          throw new Error('Failed to toggle plugin')
        }
        
        return response
      } catch (error) {
        this.error = error.message || 'Failed to toggle plugin'
        console.error('Error toggling plugin:', error)
        throw error
      }
    },

    async fetchPages() {
      try {
        if (this.pages.length > 0) {
          return this.pages
        }
        
        const response = await api.get('/htpm/v1/pages')
        this.pages = response.data
        return this.pages
      } catch (error) {
        console.error('Error fetching pages:', error)
        return []
      }
    },
    
    async fetchPosts() {
      try {
        if (this.posts.length > 0) {
          return this.posts
        }
        
        const response = await api.get('/htpm/v1/posts')
        this.posts = response.data
        return this.posts
      } catch (error) {
        console.error('Error fetching posts:', error)
        return []
      }
    },
    
    async fetchPostTypes() {
      try {
        if (this.postTypes.length > 0) {
          return this.postTypes
        }
        
        const response = await api.get('/htpm/v1/post-types')
        this.postTypes = response.data
        return this.postTypes
      } catch (error) {
        console.error('Error fetching post types:', error)
        return []
      }
    },
    
    async fetchCustomPostTypeItems(postType) {
      try {
        if (this.customPostTypeItems[postType]?.length > 0) {
          return this.customPostTypeItems[postType]
        }
        
        const response = await api.get(`/htpm/v1/post-type-items/${postType}`)
        this.customPostTypeItems[postType] = response.data
        return response.data
      } catch (error) {
        console.error(`Error fetching ${postType} items:`, error)
        return []
      }
    }
  }
})