import { defineStore } from 'pinia'
import axios from 'axios'

console.log(window.htpmData, window.HTPMM);

// Create an axios instance with WordPress REST API base URL and nonce
const api = axios.create({
  baseURL: window.htpmData.restUrl,  // Make sure this points to the correct URL
  headers: {
    'X-WP-Nonce': window.htpmData.nonce,
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
    activePlugins: (state) => state.plugins.filter(p => p.active),
    inactivePlugins: (state) => state.plugins.filter(p => !p.active),
    updateAvailable: (state) => state.plugins.filter(p => p.hasUpdate),
    totalPlugins: (state) => state.plugins.length,
    pluginById: (state) => (id) => state.plugins.find(p => p.id === id),
    customPostTypes: (state) => state.postTypes.filter(type => !['page', 'post'].includes(type.name)).map(type => type.name)
  },

  actions: {
    async fetchPlugins() {
      console.log('Fetching plugins...')
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get('/htpm/v1/plugins')
        this.plugins = response.data
        
        // Load initial settings for each plugin
        this.plugins.forEach(plugin => {
          this.fetchPluginSettings(plugin.id)
        })
        
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
        
        // Store settings
        this.settings[pluginId] = response.data
        
        return response.data
      } catch (error) {
        console.error(`Error fetching settings for plugin ${pluginId}:`, error)
        return null
      }
    },

    async togglePlugin(plugin) {
      try {
        const response = await api.post(`/htpm/v1/plugins/${plugin.id}/toggle`)
        
        if (response.data.success) {
          // Update the plugin's active state
          const index = this.plugins.findIndex(p => p.id === plugin.id)
          if (index !== -1) {
            this.plugins[index].active = response.data.active
          }
          
          // Update settings to match new state
          if (this.settings[plugin.id]) {
            this.settings[plugin.id].enable_deactivation = response.data.active ? 'no' : 'yes'
          }
        }
        
        return response.data
      } catch (error) {
        this.error = error.message || 'Failed to toggle plugin'
        console.error('Error toggling plugin:', error)
        throw error
      }
    },

    async updatePluginSettings(pluginId, settings) {
      try {
        const response = await api.post(`/htpm/v1/plugins/${pluginId}/settings`, settings)
        
        if (response.data.success) {
          // Update settings in store
          this.settings[pluginId] = { ...settings }
          
          // Update plugin active state based on settings
          const index = this.plugins.findIndex(p => p.id === pluginId)
          if (index !== -1) {
            this.plugins[index].active = settings.enable_deactivation !== 'yes'
          }
        }
        
        return response.data
      } catch (error) {
        this.error = error.message || 'Failed to update plugin settings'
        console.error('Error updating plugin settings:', error)
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